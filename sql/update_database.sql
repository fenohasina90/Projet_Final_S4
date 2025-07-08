USE final_S4;

-- Ajouter le champ mois_delai à la table prets
ALTER TABLE prets ADD COLUMN mois_delai INT DEFAULT 0;

-- Mettre à jour les prêts existants avec un délai de 0 mois
UPDATE prets SET mois_delai = 0 WHERE mois_delai IS NULL; 