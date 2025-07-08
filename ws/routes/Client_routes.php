<?php 
require_once __DIR__ . '/../controllers/ClientController.php';

Flight::route('GET /clients', ['ClientController', 'getAll']);
Flight::route('GET /clients/@id', ['ClientController', 'getById']);
?> 