<?php
require_once __DIR__ . '/../models/Pret.php';

class TypePretController {
    public static function create() {
        $data = Flight::request()->data;
        $id = Pret::createTypePret($data);
        Flight::json(['id' => $id]);
    }
} 