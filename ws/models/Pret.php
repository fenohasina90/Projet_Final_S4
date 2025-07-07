<?php
require_once __DIR__ . '/../db.php';


class Pret
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllTypePret()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM types_pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createPret($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO prets (client_id, type_pret_id, montant, date_debut, duree_mois, taux_applique) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data->client, $data->type_pret, $data->montant, $data->date_debut, $data->duree, $data->statut, $data->taux_applique]);
        return $db->lastInsertId();
    }

    
    public static function createHistoriquePret($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO historique_pret (pret_id, mois, montant_mensualite) VALUES (?, ?, ?)");
        return $stmt->execute([$data->pret_id, $data->mois, $data->montant_mensualite]);
    }

    public static function genererHistoriquePret($typeAmortissement, $pretId, $montant, $duree, $tauxAnnuel, $dateDebut) {
        switch ($typeAmortissement) {
            case 'CONSTANT':
                $details = self::amortissement_constant($montant, $tauxAnnuel, $duree);
                $mensualites = $details['mensualites'];
                break;
    
            case 'MENSUALITES_FIXES':
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
    
        $currentDate = new DateTime($dateDebut);
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

    
}
