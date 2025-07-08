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

    public static function createPret($data)
    {
        $db = getDB();
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
        switch ($typeAmortissement) {
            case 'CONSTANT':
                $details = self::amortissement_constant($montant, $tauxAnnuel, $duree);
                $mensualites = $details['mensualites'];
                break;
    
            case 'MENSALITES_FIXES':
                $details = self::mensualites_fixes($montant, $tauxAnnuel, $duree);
                $mensualites = array_fill(0, $duree, $details['mensualite']);
                break;
    
            case 'NON_AMORTISSABLE':
                $details = self::pret_non_amortissable($montant, $tauxAnnuel, $duree);
                $mensualites = array_fill(0, $duree - 1, $details['mensualite_interet']);
                $mensualites[] = $details['mensualite_interet'] + $montant; // dernier mois = intérêt + capital
                break;
    
            default:
                throw new Exception("Type d'amortissement inconnu : $typeAmortissement");
        }
    
        // Calculer la date de début de remboursement en ajoutant le délai
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

    public static function mensualites_fixes($montant, $taux_annuel, $duree_mois, $frais_dossier = 0)
    {
        $taux_mensuel = $taux_annuel / 12 / 100;
        $mensualite = $montant * ($taux_mensuel / (1 - pow(1 + $taux_mensuel, -$duree_mois)));
        $total_paye = $mensualite * $duree_mois + $frais_dossier;

        return [
            'mensualite' => round($mensualite, 2),
            'total_interets' => round($total_paye - $montant - $frais_dossier, 2),
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
}
