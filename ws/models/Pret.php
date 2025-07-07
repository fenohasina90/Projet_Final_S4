<?php 
require_once __DIR__ . '/../db.php';


class Pret {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createTypePret($data){
        $db = getDB();
        $stmt =$db->prepare("INSERT INTO prets (nom,taux_annuel,duree_max_mois,montant_min,montant_max,frais_dossier,type_amortissement) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$data->nom, $data->prenom, $data->email, $data->age]);
        return $db->lastInsertId();
    }
}

?>