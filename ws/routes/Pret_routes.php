<?php 
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('GET /type-pret', ['PretController', 'getAllType']);
Flight::route('POST /simuler-mensualite-fixe', ['PretController', 'simulerMensualiteFixe']);
Flight::route('POST /valider-pret', ['PretController', 'createPret']);
Flight::route('GET /prets', ['PretController', 'getAll']);
Flight::route('POST /approve-pret', ['PretController', 'approvePret']);
Flight::route('GET /historique-pret', ['PretController', 'getHistorique']);
?>