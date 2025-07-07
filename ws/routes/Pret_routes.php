<?php 
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('GET /type-pret', ['PretController', 'getAllType']);
?>