<?php
require_once __DIR__ . '/../db.php';


class Pret
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT p.*, sp.statut FROM prets p 
                           LEFT JOIN (
                               SELECT pret_id, statut 
                               FROM statuts_pret sp1 
                               WHERE date_statut = (
                                   SELECT MAX(date_statut) 
                                   FROM statuts_pret sp2 
                                   WHERE sp2.pret_id = sp1.pret_id
                               )
                           ) sp ON p.pret_id = sp.pret_id 
                           ORDER BY p.date_debut DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllTypePret()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM types_pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($pretId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT p.*, sp.statut FROM prets p 
                              LEFT JOIN (
                                  SELECT pret_id, statut 
                                  FROM statuts_pret sp1 
                                  WHERE date_statut = (
                                      SELECT MAX(date_statut) 
                                      FROM statuts_pret sp2 
                                      WHERE sp2.pret_id = sp1.pret_id
                                  )
                              ) sp ON p.pret_id = sp.pret_id 
                              WHERE p.pret_id = ?");
        $stmt->execute([$pretId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updatePretStatus($pretId, $statut)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO statuts_pret (pret_id, statut) VALUES (?, ?)");
        return $stmt->execute([$pretId, $statut]);
    }

    public static function updatePret($pretId, $data)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE prets SET mois_delai = ? WHERE pret_id = ?");
        return $stmt->execute([$data['mois_delai'], $pretId]);
    }

    public static function getTypePretById($typePretId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM types_pret WHERE type_pret_id = ?");
        $stmt->execute([$typePretId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getFondsDisponible($db) {
        // Calcule le total des crédits - débits dans la table soldes
        $sql = "SELECT SUM(CASE WHEN type_solde = 'Crédit' THEN montant ELSE 0 END) - SUM(CASE WHEN type_solde = 'Débit' THEN montant ELSE 0 END) AS fonds_disponible FROM soldes";
        $stmt = $db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? floatval($row['fonds_disponible']) : 0;
    }

    public static function createPret($data)
    {
        $db = getDB();
        // Vérification fonds disponible
        $fonds_disponible = self::getFondsDisponible($db);
        if ($fonds_disponible < $data['montant']) {
            throw new Exception("Fonds insuffisants pour accorder ce prêt. Fonds disponibles : " . $fonds_disponible . " €");
        }
        $stmt = $db->prepare("INSERT INTO prets (client_id, type_pret_id, montant, date_debut, duree_mois, taux_applique, assurance, mois_delai) VALUES (?, ?, ?, CURRENT_DATE, ?, ?, ?, ?)");
        $stmt->execute([
            $data['client_id'], 
            $data['type_pret_id'], 
            $data['montant'], 
            $data['duree_mois'], 
            $data['taux_applique'],
            $data['assurance'],
            $data['mois_delai'] ?? 0
        ]);
        $pretId = $db->lastInsertId();
        
        // Insérer le statut initial dans la table statuts_pret
        $stmt = $db->prepare("INSERT INTO statuts_pret (pret_id, statut) VALUES (?, ?)");
        $stmt->execute([$pretId, $data['statut']]);
        
        return $pretId;
    }

    public static function createHistoriquePret($pretId, $mois, $montantMensualite)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO historique_pret (pret_id, mois, montant_mensualite) VALUES (?, ?, ?)");
        return $stmt->execute([$pretId, $mois, $montantMensualite]);
    }

    public static function genererHistoriquePret($typeAmortissement, $pretId, $montant, $duree, $tauxAnnuel, $dateDebut, $moisDelai = 0) {
        $dureeEffective = $duree - $moisDelai;
    
        if ($dureeEffective <= 0) {
            throw new Exception("La durée du prêt doit être supérieure au délai de grâce.");
        }
    
        switch ($typeAmortissement) {
            case 'CONSTANT':
                $details = self::amortissement_constant($montant, $tauxAnnuel, $dureeEffective);
                $mensualites = $details['mensualites'];
                break;
    
            case 'MENSALITES_FIXES':
                $details = self::mensualites_fixes($montant, $tauxAnnuel, $dureeEffective);
                $mensualites = array_fill(0, $dureeEffective, $details['mensualite']);
                break;
    
            case 'NON_AMORTISSABLE':
                $details = self::pret_non_amortissable($montant, $tauxAnnuel, $dureeEffective);
                $mensualites = array_fill(0, $dureeEffective - 1, $details['mensualite_interet']);
                $mensualites[] = $details['mensualite_interet'] + $montant; // Dernier mois
                break;
    
            default:
                throw new Exception("Type d'amortissement inconnu : $typeAmortissement");
        }
    
        $dateDebutRemboursement = new DateTime($dateDebut);
        $dateDebutRemboursement->modify("+{$moisDelai} months");

        $currentDate = clone $dateDebutRemboursement;
        foreach ($mensualites as $mensualite) {
            $mois = $currentDate->format('Y-m-d');
            self::createHistoriquePret($pretId, $mois, $mensualite);
            $currentDate->modify('+1 month');
        }
    
        return true;
    }
    
    


    public static function amortissement_constant($montant, $taux_annuel, $duree_mois, $frais_dossier = 0)
    {
        $taux_mensuel = $taux_annuel / 12 / 100;
        $amortissement = $montant / $duree_mois;
        $mensualites = [];
        $capital_restant = $montant;
        $total_interets = 0;

        for ($i = 0; $i < $duree_mois; $i++) {
            $interet = $capital_restant * $taux_mensuel;
            $mensualite = $amortissement + $interet;
            $mensualites[] = round($mensualite, 2);
            $total_interets += $interet;
            $capital_restant -= $amortissement;
        }

        $total_paye = array_sum($mensualites) + $frais_dossier;

        return [
            'mensualites' => $mensualites,
            'total_interets' => round($total_interets, 2),
            'total_paye' => round($total_paye, 2)
        ];
    }

    public static function mensualites_fixes($montant, $taux_annuel, $duree_mois, $assurance = 0)
    {
        $taux_mensuel = $taux_annuel / 12 / 100;
        $assurance = $assurance / 12 / 100;
        $mensualite = $montant * ($taux_mensuel / (1 - pow(1 + $taux_mensuel, -$duree_mois)))+$assurance;
        $total_paye = $mensualite * $duree_mois;

        return [
            'mensualite' => round($mensualite, 2),
            'total_interets' => round($total_paye - $montant - $assurance, 2),
            'total_paye' => round($total_paye, 2)
        ];
    }

    public static function pret_non_amortissable($montant, $taux_annuel, $duree_mois, $frais_dossier = 0)
    {
        $taux_mensuel = $taux_annuel / 12 / 100;
        $mensualite_interet = $montant * $taux_mensuel;
        $total_interets = $mensualite_interet * $duree_mois;
        $total_paye = $total_interets + $montant + $frais_dossier;

        return [
            'mensualite_interet' => round($mensualite_interet, 2),
            'total_interets' => round($total_interets, 2),
            'total_paye' => round($total_paye, 2)
        ];
    }

    public static function getHistorique($clientId = null)
    {
        $db = getDB();
        $sql = "SELECT hp.*, p.client_id, p.montant, p.duree_mois, p.taux_applique 
                FROM historique_pret hp 
                JOIN prets p ON hp.pret_id = p.pret_id 
                JOIN (
                    SELECT pret_id, statut 
                    FROM statuts_pret sp1 
                    WHERE date_statut = (
                        SELECT MAX(date_statut) 
                        FROM statuts_pret sp2 
                        WHERE sp2.pret_id = sp1.pret_id
                    )
                ) sp ON p.pret_id = sp.pret_id 
                WHERE sp.statut = 'Approuvé'";
        
        if ($clientId) {
            $sql .= " AND p.client_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$clientId]);
        } else {
            $stmt = $db->query($sql);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getInteretParMois($date1, $date2)
    {
        $db = getDB();
        
        // Calculer les intérêts par mois pour les prêts approuvés
        // Pour simplifier, on considère que les intérêts représentent environ 30% du montant mensuel
        // Dans un vrai système, il faudrait calculer précisément la part intérêts vs capital
        $sql = "SELECT 
                    DATE_FORMAT(hp.mois, '%Y-%m') as periode,
                    SUM(hp.montant_mensualite * 0.3) as interet_gagne
                FROM historique_pret hp 
                JOIN prets p ON hp.pret_id = p.pret_id 
                JOIN (
                    SELECT pret_id, statut 
                    FROM statuts_pret sp1 
                    WHERE date_statut = (
                        SELECT MAX(date_statut) 
                        FROM statuts_pret sp2 
                        WHERE sp2.pret_id = sp1.pret_id
                    )
                ) sp ON p.pret_id = sp.pret_id 
                WHERE sp.statut = 'Approuvé'
                AND hp.mois BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(hp.mois, '%Y-%m')
                ORDER BY periode";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$date1, $date2]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createTypePret($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO types_pret (nom, taux_annuel, duree_max_mois, montant_min, montant_max, frais_dossier, type_amortissement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nom'],
            $data['taux_annuel'],
            $data['duree_max_mois'],
            $data['montant_min'],
            $data['montant_max'],
            $data['frais_dossier'],
            $data['type_amortissement']
        ]);
        return $db->lastInsertId();
    }

    public static function payerMensualite($historiqueId)
    {
        $db = getDB();
        // Récupérer le montant de la mensualité et la date
        $stmt = $db->prepare("SELECT montant_mensualite, pret_id, mois FROM historique_pret WHERE historique_id = ?");
        $stmt->execute([$historiqueId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $montant = $row['montant_mensualite'];
        $pret_id = $row['pret_id'];
        $mois = $row['mois'];

        // Marquer comme payé
        $stmt = $db->prepare("UPDATE historique_pret SET statut_paiement = 'Payé', date_paiement = CURRENT_DATE WHERE historique_id = ?");
        $stmt->execute([$historiqueId]);

        // Créditer le solde
        $desc = "Remboursement prêt ID $pret_id - échéance $mois";
        $stmt = $db->prepare("INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description) VALUES (NULL, CURRENT_DATE, ?, 'Crédit', ?)");
        $stmt->execute([$montant, $desc]);
        return true;
    }

    public static function getHistoriquePaiement($clientId = null)
    {
        $db = getDB();
        $sql = "SELECT hp.*, p.client_id, p.montant, p.duree_mois, p.taux_applique, p.pret_id, c.nom as client_nom
                FROM historique_pret hp
                JOIN prets p ON hp.pret_id = p.pret_id
                JOIN clients c ON p.client_id = c.client_id
                JOIN (
                    SELECT pret_id, statut
                    FROM statuts_pret sp1
                    WHERE date_statut = (
                        SELECT MAX(date_statut)
                        FROM statuts_pret sp2
                        WHERE sp2.pret_id = sp1.pret_id
                    )
                ) sp ON p.pret_id = sp.pret_id
                WHERE sp.statut = 'Approuvé'";
        if ($clientId) {
            $sql .= " AND p.client_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$clientId]);
        } else {
            $stmt = $db->query($sql);
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcul du reste à payer par prêt
        $restes = [];
        foreach ($result as $row) {
            $pretId = $row['pret_id'];
            if (!isset($restes[$pretId])) {
                $restes[$pretId] = [
                    'pret_id' => $pretId,
                    'client_id' => $row['client_id'],
                    'client_nom' => $row['client_nom'],
                    'montant' => $row['montant'],
                    'duree_mois' => $row['duree_mois'],
                    'taux_applique' => $row['taux_applique'],
                    'mensualites' => [],
                    'reste_a_payer' => 0
                ];
            }
            $restes[$pretId]['mensualites'][] = $row;
            if ($row['statut_paiement'] !== 'Payé') {
                $restes[$pretId]['reste_a_payer'] += $row['montant_mensualite'];
            }
        }
        return array_values($restes);
    }

    public static function getDisponibiliteEF($dateDebut, $dateFin)
    {
        $db = getDB();
        // Fonds initiaux = somme des montants max de tous les types de prêts (ou à adapter selon ta logique)
        $fonds_initiaux = $db->query("SELECT SUM(montant_max) as total FROM types_pret")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Générer la liste des mois entre dateDebut et dateFin
        $start = new DateTime($dateDebut.'-01');
        $end = new DateTime($dateFin.'-01');
        $end->modify('last day of this month');
        $mois = [];
        $current = clone $start;
        while ($current <= $end) {
            $mois[] = $current->format('Y-m');
            $current->modify('+1 month');
        }

        $result = [];
        $fonds_non_empruntes = $fonds_initiaux;
        $total_pret_accorde = 0;
        $total_remboursements = 0;

        foreach ($mois as $m) {
            // Prêts accordés jusqu'à la fin de ce mois
            $pret_accorde = $db->prepare("SELECT SUM(montant) as total FROM prets WHERE DATE_FORMAT(date_debut, '%Y-%m') <= ?");
            $pret_accorde->execute([$m]);
            $total_pret_accorde = $pret_accorde->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            $fonds_non_empruntes = $fonds_initiaux - $total_pret_accorde;
            if ($fonds_non_empruntes < 0) $fonds_non_empruntes = 0;

            // Remboursements reçus jusqu'à la fin de ce mois
            $remboursements = $db->prepare("SELECT SUM(montant_mensualite) as total FROM historique_pret WHERE statut_paiement = 'Payé' AND DATE_FORMAT(date_paiement, '%Y-%m') <= ?");
            $remboursements->execute([$m]);
            $total_remboursements = $remboursements->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $result[] = [
                'mois' => $m,
                'fonds_non_empruntes' => round($fonds_non_empruntes, 2),
                'remboursements_recus' => round($total_remboursements, 2),
                'total_disponible' => round($fonds_non_empruntes + $total_remboursements, 2)
            ];
        }
        return $result;
    }

    public static function debiterFondsPourPret($montant, $description = null) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description) VALUES (NULL, CURRENT_DATE, ?, 'Débit', ?)");
        $stmt->execute([$montant, $description ?? 'Déblocage fonds prêt']);
    }

    public static function enregistrerSimulation($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO simulation (type_pret_id, montant, duree_mois, taux_applique, resultat, date_simulation) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['type_pret_id'],
            $data['montant'],
            $data['duree_mois'],
            $data['taux_applique'],
            $data['resultat'] ?? null
        ]);
        return $db->lastInsertId();
    }

    public static function getSimulations()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM simulation ORDER BY date_simulation DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
