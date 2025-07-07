CREATE TABLE clients (
    client_id SERIAL PRIMARY KEY,
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
    compte_id SERIAL PRIMARY KEY,
    client_id INT REFERENCES clients(client_id),
    type_compte VARCHAR(50) CHECK (type_compte IN ('Courant', 'Épargne', 'Entreprise')),
    date_ouverture DATE DEFAULT CURRENT_DATE,
    iban VARCHAR(34) UNIQUE
);

CREATE TABLE soldes (
    solde_id SERIAL PRIMARY KEY,
    compte_id INT REFERENCES comptes(compte_id),
    date_solde DATE NOT NULL,
    montant DECIMAL(12, 2) NOT NULL,
    type_solde VARCHAR(20) CHECK (type_solde IN ('Crédit', 'Débit')),
    description TEXT
);

CREATE TABLE types_pret (
    type_pret_id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux_annuel DECIMAL(5, 2) NOT NULL,
    duree_max_mois INT,
    montant_min DECIMAL(10, 2),
    montant_max DECIMAL(10, 2),
    frais_dossier DECIMAL(5, 2),
    type_amortissement VARCHAR(20) CHECK (type_amortissement IN (
        'CONSTANT',         -- Amortissement constant (mensualités décroissantes)
        'MENSALITES_FIXES', -- Mensualités fixes (classique)
        'NON_AMORTISSABLE',          -- Non amortissable (capital final)
        'MODULABLE'         -- Mensualités variables
    )) NOT NULL
);


CREATE TABLE prets (
    pret_id SERIAL PRIMARY KEY,
    client_id INT REFERENCES clients(client_id),
    type_pret_id INT REFERENCES types_pret(type_pret_id),
    montant DECIMAL(12, 2) NOT NULL,
    date_debut DATE DEFAULT CURRENT_DATE,
    duree_mois INT NOT NULL,
    statut VARCHAR(20) CHECK (statut IN ('En attente', 'Approuvé', 'Refusé', 'Remboursé')),
    mensualite DECIMAL(10, 2),
    taux_applique DECIMAL(5, 2) NOT NULL
);
CREATE TABLE amortissements (
    amortissement_id SERIAL PRIMARY KEY,
    pret_id INT REFERENCES prets(pret_id),
    mois INT NOT NULL,
    date_echeance DATE NOT NULL,
    capital_restant DECIMAL(12, 2) NOT NULL,
    part_capital DECIMAL(10, 2) NOT NULL,
    part_interets DECIMAL(10, 2) NOT NULL,
    mensualite DECIMAL(10, 2) NOT NULL,
    statut VARCHAR(20) DEFAULT 'A venir' CHECK (statut IN ('A venir', 'Payé', 'En retard'))
);
CREATE TABLE transactions (
    transaction_id SERIAL PRIMARY KEY,
    compte_id INT REFERENCES comptes(compte_id),
    montant DECIMAL(12, 2) NOT NULL,
    type_transaction VARCHAR(50) CHECK (type_transaction IN (
        'Dépôt', 
        'Retrait', 
        'Virement', 
        'Prélèvement',
        'Remboursement'
    )),
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    pret_id INT NULL REFERENCES prets(pret_id)
);

CREATE TABLE taux_interet (
    taux_id SERIAL PRIMARY KEY,
    type_produit VARCHAR(50) NOT NULL,
    taux_annuel DECIMAL(5, 2) NOT NULL,
    date_mise_a_jour DATE DEFAULT CURRENT_DATE
);