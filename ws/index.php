<?php
require 'vendor/autoload.php';
require 'db.php';

$db = getDB(); // Connexion globale à la base

require 'routes/etudiant_routes.php';
require 'routes/Pret_routes.php';
require 'routes/Utilisateurs_route.php';

Flight::start();