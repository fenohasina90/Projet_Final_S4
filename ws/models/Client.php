<?php
require_once __DIR__ . '/../db.php';

class Client
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM clients ORDER BY nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($clientId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM clients WHERE client_id = ?");
        $stmt->execute([$clientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?> 