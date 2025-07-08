CREATE DATABASE final_S4;
USE final_S4;

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
    CHECK (type_compte IN ('Courant', 'Épargne', 'Entreprise'))
);

CREATE TABLE soldes (
    solde_id INT AUTO_INCREMENT PRIMARY KEY,
    compte_id INT,
    date_solde DATE NOT NULL,
    montant DECIMAL(12, 2) NOT NULL,
    type_solde VARCHAR(20),
    description TEXT,
    FOREIGN KEY (compte_id) REFERENCES comptes(compte_id),
    CHECK (type_solde IN ('Crédit', 'Débit'))
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
    montant DECIMAL(12,2) NOT NULL,
    date_debut DATE DEFAULT CURRENT_DATE,
    duree_mois INT NOT NULL,
    taux_applique DECIMAL(5,2) NOT NULL,
    assurance INT DEFAULT 0,
    FOREIGN KEY (client_id) REFERENCES clients(client_id),
    FOREIGN KEY (type_pret_id) REFERENCES types_pret(type_pret_id)
);

alter Table prets add COLUMN assurance INT DEFAULT 0;




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

CREATE TABLE statuts_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pret_id INT,
    statut VARCHAR(20),
    date_statut DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pret_id) REFERENCES prets(pret_id),
    CHECK (statut IN ('En attente', 'Approuve', 'Refuse', 'Rembourse'))
);


CREATE TABLE historique_pret (
    historique_id INT AUTO_INCREMENT PRIMARY KEY,
    pret_id INT,
    mois DATE NOT NULL,
    montant_mensualite DECIMAL(10, 2),
    FOREIGN KEY (pret_id) REFERENCES prets(pret_id)
);
    -- statut_paiement VARCHAR(20) DEFAULT 'Non payé',
    -- date_paiement DATE,  -- date réelle de paiement
