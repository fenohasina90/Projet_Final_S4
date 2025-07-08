USE final_S4;

-- Insérer des clients de test
INSERT INTO clients (nom, prenom, email, telephone, adresse, date_naissance, revenu_mensuel, score_credit) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', '0123456789', '123 Rue de la Paix, Paris', '1985-03-15', 3500.00, 750),
('Martin', 'Marie', 'marie.martin@email.com', '0987654321', '456 Avenue des Champs, Lyon', '1990-07-22', 4200.00, 800),
('Bernard', 'Pierre', 'pierre.bernard@email.com', '0555666777', '789 Boulevard Central, Marseille', '1982-11-08', 3800.00, 720);

-- Insérer des types de prêts
INSERT INTO types_pret (nom, taux_annuel, duree_max_mois, montant_min, montant_max, frais_dossier, type_amortissement) VALUES
('Prêt Personnel', 4.50, 60, 1000.00, 50000.00, 50.00, 'MENSALITES_FIXES'),
('Prêt Immobilier', 2.80, 300, 50000.00, 500000.00, 200.00, 'MENSALITES_FIXES'),
('Prêt Auto', 3.20, 84, 5000.00, 30000.00, 100.00, 'CONSTANT'),
('Prêt Étudiant', 1.50, 120, 1000.00, 15000.00, 25.00, 'NON_AMORTISSABLE'); 