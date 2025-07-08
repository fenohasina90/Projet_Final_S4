<?php
require_once __DIR__ . '/../models/Pret.php';

class InteretController {
    public static function getInteretParMois() {
        $request = Flight::request();
        $date1 = $request->query->date1;
        $date2 = $request->query->date2;
        
        try {
            $interets = Pret::getInteretParMois($date1, $date2);
            Flight::json($interets);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()]);
        }
    }
}
?> 