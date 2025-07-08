<?php
class Compte {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    // Retourne la liste des comptes avec nom du client et type
    public function getAll() {
        $sql = "SELECT c.compte_id, c.type_compte, cl.nom AS nom_client, cl.prenom AS prenom_client FROM comptes c JOIN clients cl ON c.client_id = cl.client_id ORDER BY c.compte_id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 