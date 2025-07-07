<?php
require_once __DIR__ . '/../models/Compte.php';

class CompteController {
    private $compteModel;
    public function __construct($db) {
        $this->compteModel = new Compte($db);
    }
    public function getAll() {
        $comptes = $this->compteModel->getAll();
        Flight::json($comptes);
    }
} 