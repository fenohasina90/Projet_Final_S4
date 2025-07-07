<?php 
require_once __DIR__ . '/../db.php';
class Clients {
    public static function getAll(){
        $db = getDB();
        $stmt = $db->query("SELECT client_id, nom FROM clients");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>