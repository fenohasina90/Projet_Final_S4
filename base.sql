CREATE TABLE clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telephone VARCHAR(20),
    adresse TEXT,
    date_naissance DATE,
    revenu_mensuel DECIMAL(10, 2),
    score_credit INT
);

CREATE TABLE comptes (
    compte_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    type_compte VARCHAR(50),
    date_ouverture DATE DEFAULT CURRENT_DATE,
    iban VARCHAR(34) UNIQUE,
    FOREIGN KEY (client_id) REFERENCES clients(client_id),
    CHECK (type_compte IN ('Courant', 'Epargne', 'Entreprise'))
);

CREATE TABLE soldes (
    solde_id INT AUTO_INCREMENT PRIMARY KEY,
    compte_id INT,
    date_solde DATE NOT NULL,
    montant DECIMAL(12, 2) NOT NULL,
    type_solde VARCHAR(20),
    description TEXT,
    FOREIGN KEY (compte_id) REFERENCES comptes(compte_id),
    CHECK (type_solde IN ('Credit', 'Debit'))
);

CREATE TABLE types_pret (
    type_pret_id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux_annuel DECIMAL(5, 2) NOT NULL,
    duree_max_mois INT,
    montant_min DECIMAL(10, 2),
    montant_max DECIMAL(10, 2),
    frais_dossier DECIMAL(5, 2),
    type_amortissement VARCHAR(20) NOT NULL,
    CHECK (type_amortissement IN (
        'CONSTANT',
        'MENSALITES_FIXES',
        'NON_AMORTISSABLE',
        'MODULABLE'
    ))
);

    CREATE TABLE prets (
        pret_id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT,
        type_pret_id INT,
        montant DECIMAL(12, 2) NOT NULL,
        date_debut DATE DEFAULT CURRENT_DATE,
        duree_mois INT NOT NULL,
        statut VARCHAR(20),
        mensualite DECIMAL(10, 2),
        taux_applique DECIMAL(5, 2) NOT NULL,
        FOREIGN KEY (client_id) REFERENCES clients(client_id),
        FOREIGN KEY (type_pret_id) REFERENCES types_pret(type_pret_id),
        CHECK (statut IN ('En attente', 'Approuvé', 'Refusé', 'Remboursé'))
    );

CREATE TABLE amortissements (
    amortissement_id INT AUTO_INCREMENT PRIMARY KEY,
    pret_id INT,
    mois INT NOT NULL,
    date_echeance DATE NOT NULL,
    capital_restant DECIMAL(12, 2) NOT NULL,
    part_capital DECIMAL(10, 2) NOT NULL,
    part_interets DECIMAL(10, 2) NOT NULL,
    mensualite DECIMAL(10, 2) NOT NULL,
    statut VARCHAR(20) DEFAULT 'A venir',
    FOREIGN KEY (pret_id) REFERENCES prets(pret_id),
    CHECK (statut IN ('A venir', 'Paye', 'En retard'))
);

CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    compte_id INT,
    montant DECIMAL(12, 2) NOT NULL,
    type_transaction VARCHAR(50),
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    pret_id INT,
    FOREIGN KEY (compte_id) REFERENCES comptes(compte_id),
    FOREIGN KEY (pret_id) REFERENCES prets(pret_id),
    CHECK (type_transaction IN (
        'Depot',
        'Retrait',
        'Virement',
        'Prelevement',
        'Remboursement'
    ))
);


CREATE TABLE taux_interet (
    taux_id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(50) NOT NULL,
    taux_annuel DECIMAL(5, 2) NOT NULL,
    date_mise_a_jour DATE DEFAULT CURRENT_DATE
);

INSERT INTO clients (nom, prenom, email, telephone, adresse, date_naissance, revenu_mensuel, score_credit)
VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', '0600000001', '1 rue de Paris', '1980-01-01', 2500.00, 700),
('Martin', 'Sophie', 'sophie.martin@email.com', '0600000002', '2 avenue de Lyon', '1990-05-15', 3200.00, 750),
('Durand', 'Paul', 'paul.durand@email.com', '0600000003', '3 boulevard de Nice', '1985-09-20', 1800.00, 650);

INSERT INTO comptes (client_id, type_compte, iban)
VALUES
(1, 'Courant', 'FR761234598765432100000001'),
(1, 'Épargne', 'FR761234598765432100000002'),
(2, 'Courant', 'FR761234598765432100000003'),
(3, 'Courant', 'FR761234598765432100000004');

-- Dépôts pour le compte 1 (Jean Dupont)
INSERT INTO transactions (compte_id, montant, type_transaction, description)
VALUES
(1, 500.00, 'Depot', 'Premier dépôt'),
(1, 200.00, 'Depot', 'Virement salaire');

INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description)
VALUES
(1, '2024-06-01', 500.00, 'Crédit', 'Premier dépôt'),
(1, '2024-06-05', 200.00, 'Crédit', 'Virement salaire');

-- Dépôt pour le compte 2 (Jean Dupont épargne)
INSERT INTO transactions (compte_id, montant, type_transaction, description)
VALUES
(2, 1000.00, 'Dépôt', 'Épargne initiale');

INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description)
VALUES
(2, '2024-06-02', 1000.00, 'Crédit', 'Épargne initiale');

-- Dépôt pour le compte 3 (Sophie Martin)
INSERT INTO transactions (compte_id, montant, type_transaction, description)
VALUES
(3, 1500.00, 'Dépôt', 'Salaire Sophie');

INSERT INTO soldes (compte_id, date_solde, montant, type_solde, description)
VALUES
(3, '2024-06-03', 1500.00, 'Crédit', 'Salaire Sophie');

UPDATE transactions SET type_transaction = 'Dépôt', description = REPLACE(description, '?', 'é') WHERE type_transaction LIKE 'D?p?t';