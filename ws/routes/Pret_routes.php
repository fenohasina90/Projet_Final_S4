<?php 
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('/POST/type_pret',['PretController', 'create_type_pret']);
?>