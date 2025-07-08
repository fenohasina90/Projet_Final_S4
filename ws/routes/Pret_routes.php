<?php 
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('GET /type-pret', ['PretController', 'getAllType']);
Flight::route('POST /simuler-mensualite-fixe', ['PretController', 'simulerMensualiteFixe']);
Flight::route('POST /valider-pret', ['PretController', 'createPret']);
Flight::route('GET /prets', ['PretController', 'getAll']);
Flight::route('POST /approve-pret', ['PretController', 'approvePret']);
Flight::route('POST /update-pret', ['PretController', 'updatePret']);
Flight::route('GET /historique-pret', ['PretController', 'getHistorique']);
Flight::route('POST /payer-mensualite', ['PretController', 'payerMensualite']);
Flight::route('GET /historique-paiement', ['PretController', 'getHistoriquePaiement']);
Flight::route('GET /disponibilite-ef', ['PretController', 'getDisponibiliteEF']);
Flight::route('POST /enregistrer-simulation', ['PretController', 'enregistrerSimulation']);
Flight::route('GET /simulations', ['PretController', 'getSimulations']);
?>