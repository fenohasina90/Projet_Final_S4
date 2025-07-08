<?php
require_once __DIR__ . '/../controllers/TypePretController.php';

Flight::route('POST /type_pret', ['TypePretController', 'create']);
?> 