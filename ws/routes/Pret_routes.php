<?php 
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('GET /type-pret', ['PretController', 'getAllType']);
Flight::route('POST /simuler-mensualite-fixe', ['PretController', 'simulerMensualiteFixe']);
Flight::route('POST /valider-pret', ['PretController', 'createPret']);
?>