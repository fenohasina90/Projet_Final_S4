<?php
class Fonds {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ajout d'un dépôt (fonds)
    public function ajouterDepot($compte_id, $montant, $description = null) {
        // Vérifier que le montant est positif
        if (!is_numeric($montant) || $montant <= 0) {
            return ["success" => false, "error" => "Montant invalide."];
        }

        // Vérifier que le compte existe et est lié à un client
        $stmt = $this->db->prepare("SELECT c.compte_id, c.client_id, cl.client_id AS client_existe FROM comptes c JOIN clients cl ON c.client_id = cl.client_id WHERE c.compte_id = ?");
        $stmt->execute([$compte_id]);
        $compte = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$compte) {
            return ["success" => false, "error" => "Compte inexistant ou non associé à un client valide."];
        }

        // Vérifier l'encodage UTF-8 de la description
        if ($description !== null && !mb_check_encoding($description, 'UTF-8')) {
            return ["success" => false, "error" => "Description non encodée en UTF-8."];
        }

        // Type de transaction
        $type_transaction = "Dépôt";
        if (!mb_check_encoding($type_transaction, 'UTF-8')) {
            return ["success" => false, "error" => "Type de transaction corrompu."];
        }

        try {
            $this->db->beginTransaction();

            // Insérer dans transactions
            $stmt = $this->db->prepare("INSERT INTO transactions (compte_id, montant, type_transaction, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$compte_id, $montant, $type_transaction, $description]);

            // Insérer dans soldes
            $stmt = $this->db->prepare("INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description) VALUES (?, CURRENT_DATE, ?, 'Crédit', ?)");
            $stmt->execute([$compte_id, $montant, $description]);

            $this->db->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ["success" => false, "error" => "Erreur lors de l'ajout du dépôt : " . $e->getMessage()];
        }
    }

    // Liste des dépôts (fonds)
    public function getDepots() {
        $sql = "SELECT t.transaction_id, t.compte_id, t.montant, t.date_transaction, t.description, c.type_compte, cl.nom AS nom_client, cl.prenom AS prenom_client
                FROM transactions t
                JOIN comptes c ON t.compte_id = c.compte_id
                JOIN clients cl ON c.client_id = cl.client_id
                WHERE t.type_transaction LIKE 'Dep%'
                ORDER BY t.date_transaction DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retourne le solde total par compte et le total général
    public function getTotaux() {
        // Solde par compte (somme des crédits - débits)
        $sql = "SELECT c.compte_id, c.type_compte, cl.nom AS nom_client, cl.prenom AS prenom_client,
                       ABS(SUM(CASE WHEN s.type_solde = 'Crédit' THEN s.montant ELSE 0 END) -
                       SUM(CASE WHEN s.type_solde = 'Débit' THEN s.montant ELSE 0 END)) AS solde
                FROM soldes s
                JOIN comptes c ON s.compte_id = c.compte_id
                JOIN clients cl ON c.client_id = cl.client_id
                GROUP BY s.compte_id, c.type_compte, cl.nom, cl.prenom
                ORDER BY s.compte_id";
        $stmt = $this->db->query($sql);
        $par_compte = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Total général
        $total = 0;
        foreach ($par_compte as $row) {
            $total += abs($row['solde']);
        }
        // Calculer total crédits et total débits
        $sql2 = "SELECT type_solde, SUM(montant) as total FROM soldes GROUP BY type_solde";
        $stmt2 = $this->db->query($sql2);
        $credits = 0; $debits = 0;
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (strtolower($r['type_solde']) === 'crédit' || strtolower($r['type_solde']) === 'credit') $credits += $r['total'];
            if (strtolower($r['type_solde']) === 'débit' || strtolower($r['type_solde']) === 'debit') $debits += $r['total'];
        }
        return [
            'par_compte' => $par_compte,
            'total_general' => $total,
            'totaux_details' => [
                'credits' => $credits,
                'debits' => $debits
            ]
        ];
    }

    // Ajout d'un débit (fonds)
    public function ajouterDebit($compte_id, $montant, $description = null) {
        // Vérifier que le montant est positif
        if (!is_numeric($montant) || $montant <= 0) {
            return ["success" => false, "error" => "Montant invalide."];
        }

        // Vérifier que le compte existe et est lié à un client
        $stmt = $this->db->prepare("SELECT c.compte_id, c.client_id, cl.client_id AS client_existe FROM comptes c JOIN clients cl ON c.client_id = cl.client_id WHERE c.compte_id = ?");
        $stmt->execute([$compte_id]);
        $compte = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$compte) {
            return ["success" => false, "error" => "Compte inexistant ou non associé à un client valide."];
        }

        // Vérifier l'encodage UTF-8 de la description
        if ($description !== null && !mb_check_encoding($description, 'UTF-8')) {
            return ["success" => false, "error" => "Description non encodée en UTF-8."];
        }

        // Type de transaction
        $type_transaction = "Retrait";
        if (!mb_check_encoding($type_transaction, 'UTF-8')) {
            return ["success" => false, "error" => "Type de transaction corrompu."];
        }

        try {
            $this->db->beginTransaction();

            // Insérer dans transactions
            $stmt = $this->db->prepare("INSERT INTO transactions (compte_id, montant, type_transaction, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$compte_id, $montant, $type_transaction, $description]);

            // Insérer dans soldes
            $stmt = $this->db->prepare("INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description) VALUES (?, CURRENT_DATE, ?, 'Débit', ?)");
            $stmt->execute([$compte_id, $montant, $description]);

            $this->db->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ["success" => false, "error" => "Erreur lors de l'ajout du débit : " . $e->getMessage()];
        }
    }

    public function create($data) {
        try {
            $this->db->beginTransaction();
            // Ajout dans fonds
            $stmt = $this->db->prepare("INSERT INTO fonds (compte_id, montant, description, date_operation) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $data['compte_id'],
                $data['montant'],
                $data['description'] ?? null
            ]);
            // Ajout dans soldes
            $stmt2 = $this->db->prepare("INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description) VALUES (?, CURRENT_DATE, ?, 'Crédit', ?)");
            $stmt2->execute([
                $data['compte_id'],
                $data['montant'],
                $data['description'] ?? null
            ]);
            $this->db->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
} 