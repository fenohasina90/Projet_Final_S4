<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../helpers/Utils.php';

class PretController {
    public static function getAll() {
        $prets = Pret::getAll();
        Flight::json($prets);
    }

    public static function getAllType() {
        $pret = Pret::getAllTypePret();
        Flight::json($pret);
    }

    // public static function getById($id) {
    //     $pret = Pret::getById($id);
    //     Flight::json($pret);
    // }

    // public static function create() {
    //     $data = Flight::request()->data;
    //     $id = Pret::create($data);
    //     Flight::json(['message' => 'Prêt ajouté', 'id' => $id]);
    // }

    // public static function update($id) {
    //     $data = Flight::request()->data;
    //     Pret::update($id, $data);
    //     Flight::json(['message' => 'Prêt modifié']);
    // }

    // public static function delete($id) {
    //     Pret::delete($id);
    //     Flight::json(['message' => 'Prêt supprimé']);
    // }

    public static function createPret() {
        $data = Flight::request()->data;
        try {
            $id = Pret::createPret($data);
            Flight::json(['success' => true, 'message' => 'Prêt créé avec succès', 'id' => $id]);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function simulerMensualiteFixe() {
        $data = Flight::request()->data;
        $montant = $data['montant'];
        $taux_annuel = $data['taux_annuel'];
        $duree = $data['duree'];
        $result = Pret::mensualites_fixes($montant, $taux_annuel, $duree);
        Flight::json(['mensualite' => $result['mensualite']]);
    }

    public static function approvePret() {
        $data = Flight::request()->data;
        try {
            $pretId = $data['pret_id'];
            $statut = $data['statut'];
            
            // Mettre à jour le statut du prêt
            Pret::updatePretStatus($pretId, $statut);
            
            // Si approuvé, générer l'historique des mensualités
            if ($statut === 'Approuvé') {
                $pret = Pret::getById($pretId);
                if ($pret) {
                    // Récupérer le type de prêt pour déterminer le type d'amortissement
                    $typePret = Pret::getTypePretById($pret['type_pret_id']);
                    $typeAmortissement = $typePret['type_amortissement'];
                    
                    // Générer l'historique des mensualités
                    Pret::genererHistoriquePret(
                        $typeAmortissement,
                        $pretId,
                        $pret['montant'],
                        $pret['duree_mois'],
                        $pret['taux_applique'],
                        $pret['date_debut']
                    );
                }
            }
            
            Flight::json(['success' => true, 'message' => 'Prêt approuvé avec succès']);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function getHistorique() {
        $request = Flight::request();
        $clientId = $request->query->client_id;
        
        try {
            $historique = Pret::getHistorique($clientId);
            Flight::json($historique);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()]);
        }
    }
}

?>