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
            
            // Mettre à jour le mois_delai si fourni
            if (isset($data['mois_delai'])) {
                Pret::updatePret($pretId, ['mois_delai' => $data['mois_delai']]);
            }

            // Mettre à jour le statut du prêt
            Pret::updatePretStatus($pretId, $statut);
            
            // Si approuvé, générer l'historique des mensualités et débiter les fonds
            if ($statut === 'Approuvé') {
                $pret = Pret::getById($pretId);
                if ($pret) {
                    // Récupérer le type de prêt pour déterminer le type d'amortissement
                    $typePret = Pret::getTypePretById($pret['type_pret_id']);
                    $typeAmortissement = $typePret['type_amortissement'];
                    
                    // Générer l'historique des mensualités avec le délai
                    Pret::genererHistoriquePret(
                        $typeAmortissement,
                        $pretId,
                        $pret['montant'],
                        $pret['duree_mois'],
                        $pret['taux_applique'],
                        $pret['date_debut'],
                        $pret['mois_delai'] ?? 0
                    );
                    // Débiter les fonds globaux
                    Pret::debiterFondsPourPret($pret['montant'], 'Déblocage fonds prêt ID ' . $pretId);
                }
            }
            
            Flight::json(['success' => true, 'message' => 'Prêt approuvé avec succès']);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function updatePret() {
        $data = Flight::request()->data;
        try {
            $pretId = $data['pret_id'];
            $moisDelai = $data['mois_delai'];
            
            // Vérifier que le prêt existe et n'est pas encore approuvé
            $pret = Pret::getById($pretId);
            if (!$pret) {
                Flight::json(['success' => false, 'message' => 'Prêt non trouvé']);
                return;
            }
            
            if ($pret['statut'] !== 'En attente') {
                Flight::json(['success' => false, 'message' => 'Impossible de modifier un prêt déjà approuvé']);
                return;
            }
            
            // Mettre à jour le prêt
            Pret::updatePret($pretId, ['mois_delai' => $moisDelai]);
            
            Flight::json(['success' => true, 'message' => 'Prêt modifié avec succès']);
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

    public static function payerMensualite() {
        $data = Flight::request()->data;
        $historiqueId = $data['historique_id'];
        try {
            Pret::payerMensualite($historiqueId);
            Flight::json(['success' => true, 'message' => 'Mensualité payée']);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function getHistoriquePaiement() {
        $clientId = Flight::request()->query->client_id;
        try {
            $data = Pret::getHistoriquePaiement($clientId);
            Flight::json($data);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function getDisponibiliteEF() {
        $dateDebut = Flight::request()->query->date_debut;
        $dateFin = Flight::request()->query->date_fin;
        try {
            $data = Pret::getDisponibiliteEF($dateDebut, $dateFin);
            Flight::json($data);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function enregistrerSimulation() {
        $data = Flight::request()->data;
        try {
            $id = Pret::enregistrerSimulation($data);
            Flight::json(['success' => true, 'message' => 'Simulation enregistrée', 'id' => $id]);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function getSimulations() {
        try {
            $simus = Pret::getSimulations();
            Flight::json($simus);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

?>