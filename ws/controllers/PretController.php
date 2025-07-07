<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../helpers/Utils.php';

class PretController {
    public static function getAll() {
        $prets = Pret::getAll();
        Flight::json($prets);
    }

    public static function getAllType() {
        $pret = Pret::getAllTypePret();
        Flight::json($pret);
    }

    // public static function getById($id) {
    //     $pret = Pret::getById($id);
    //     Flight::json($pret);
    // }

    // public static function create() {
    //     $data = Flight::request()->data;
    //     $id = Pret::create($data);
    //     Flight::json(['message' => 'Prêt ajouté', 'id' => $id]);
    // }

    // public static function update($id) {
    //     $data = Flight::request()->data;
    //     Pret::update($id, $data);
    //     Flight::json(['message' => 'Prêt modifié']);
    // }

    // public static function delete($id) {
    //     Pret::delete($id);
    //     Flight::json(['message' => 'Prêt supprimé']);
    // }

    public static function createPret() {
        $data = Flight::request()->data;
        $id = Pret::createPret($data);
        Flight::json(['message' => 'pret ajouté', 'id' => $id]);
    }
}




?>