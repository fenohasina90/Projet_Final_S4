<?php
require_once __DIR__ . '/../controllers/CompteController.php';

Flight::route('GET /comptes', function() {
    global $db;
    $controller = new CompteController($db);
    $controller->getAll();
}); 