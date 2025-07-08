<?php
require_once __DIR__ . '/../controllers/FondsController.php';

// Injection de la dépendance DB comme pour les autres modules
Flight::route('POST /fonds', function() {
    global $db;
    $controller = new FondsController($db);
    $controller->ajouterDepot();
});

Flight::route('GET /fonds', function() {
    global $db;
    $controller = new FondsController($db);
    $controller->getDepots();
});

Flight::route('GET /fonds/total', function() {
    global $db;
    $controller = new FondsController($db);
    $controller->getTotaux();
});

Flight::route('POST /fonds/debit', function() {
    global $db;
    $controller = new FondsController($db);
    $controller->ajouterDebit();
}); 