<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../helpers/Utils.php';

class PretController {
    public static function create_type_pret() {
        $data = Flight::request()->data;
        $id = Pret::createTypePret($data);
        Flight::json(['message' => 'Type de prêt créé', 'id' => $id]);
    }
}

?>