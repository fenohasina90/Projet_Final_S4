<?php
require_once __DIR__ . '/../models/Utilisateurs.php';
class ClientController {
    public static function getAll() {
        $clients = Clients::getAll();
        Flight::json($clients);
    }
}
?>