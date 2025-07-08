<?php
require_once __DIR__ . '/../models/Fonds.php';

class FondsController {
    private $fondsModel;

    public function __construct($db) {
        $this->fondsModel = new Fonds($db);
    }

    // Traitement de l'ajout de dépôt (POST)
    public function ajouterDepot() {
        $compte_id = isset($_POST['compte_id']) ? $_POST['compte_id'] : null;
        $montant = isset($_POST['montant']) ? $_POST['montant'] : null;
        $description = isset($_POST['description']) ? $_POST['description'] : null;

        $resultat = $this->fondsModel->ajouterDepot($compte_id, $montant, $description);
        if ($resultat['success']) {
            Flight::json(["success" => true]);
        } else {
            Flight::json(["success" => false, "error" => $resultat['error']]);
        }
    }

    public function getDepots() {
        $depots = $this->fondsModel->getDepots();
        Flight::json($depots);
    }

    public function getTotaux() {
        $totaux = $this->fondsModel->getTotaux();
        Flight::json($totaux);
    }

    public function ajouterDebit() {
        $compte_id = isset($_POST['compte_id']) ? $_POST['compte_id'] : null;
        $montant = isset($_POST['montant']) ? $_POST['montant'] : null;
        $description = isset($_POST['description']) ? $_POST['description'] : null;

        $resultat = $this->fondsModel->ajouterDebit($compte_id, $montant, $description);
        if ($resultat['success']) {
            Flight::json(["success" => true]);
        } else {
            Flight::json(["success" => false, "error" => $resultat['error']]);
        }
    }

    public function create() {
        $data = Flight::request()->data;
        try {
            $result = $this->fondsModel->create($data);
            if ($result['success']) {
                Flight::json(['success' => true, 'message' => 'Dépôt ajouté avec succès']);
            } else {
                Flight::json(['success' => false, 'error' => $result['error']]);
            }
        } catch (Exception $e) {
            Flight::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
} 