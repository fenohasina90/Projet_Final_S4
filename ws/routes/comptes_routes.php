<?php
require_once __DIR__ . '/../controllers/CompteController.php';
require_once __DIR__ . '/../db.php';

Flight::route('GET /comptes', function() {
    $controller = new CompteController(getDB());
    $controller->getAll();
}); 