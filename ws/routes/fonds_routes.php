<?php
require_once __DIR__ . '/../controllers/FondsController.php';
require_once __DIR__ . '/../db.php';

// Injection de la dépendance DB comme pour les autres modules
Flight::route('POST /fonds', function() {
    $controller = new FondsController(getDB());
    $controller->create();
});

Flight::route('GET /fonds', function() {
    $controller = new FondsController(getDB());
    $controller->getAll();
});

Flight::route('GET /fonds/total', function() {
    $controller = new FondsController(getDB());
    $controller->getTotaux();
});

Flight::route('POST /fonds/debit', function() {
    $controller = new FondsController(getDB());
    $controller->ajouterDebit();
}); 