# Fonctionnalités Bancaires Détaillées

## 1. Ajout de Fonds dans l'Établissement Financier

**Description** :  
Permet d'enregistrer les dépôts de fonds (espèces, virements) sur les comptes clients.

**Tables Utilisées** :
- `comptes` : Vérification du compte destinataire
- `soldes` : Mise à jour du solde du compte
- `transactions` : Journalisation de l'opération

**Processus** :
1. Vérification de l'existence du compte (table `comptes`)
2. Création d'une entrée dans `soldes` avec le nouveau montant
3. Enregistrement de la transaction dans `transactions`

**Contrôles** :
- Vérification AML pour les gros montants (>10 000€)
- Validation de l'IBAN destinataire

---

## 2. Création de Types de Prêt

**Description** :  
Configuration des différents produits de crédit proposés par la banque.

**Tables Utilisées** :
- `types_pret` : Stockage des caractéristiques du prêt
- `taux_interet` : Référence des taux applicables

**Paramètres Configurables** :
- Taux d'intérêt annuel
- Durée maximale
- Plage de montants
- Type d'amortissement (4 modes disponibles)
- Frais de dossier

**Validation** :
- Cohérence taux/durée/montant
- Unicité du nom du produit

---

## 3. Gestion des Prêts Clients

### 3.1 Demande de Prêt

**Tables Utilisées** :
- `clients` : Vérification du score crédit
- `prets` : Enregistrement de la demande
- `types_pret` : Récupération des conditions

**Processus** :
1. Calcul du taux d'endettement (revenu dans `clients`)
2. Vérification des plages dans `types_pret`
3. Création en statut "En attente" dans `prets`

### 3.2 Approbation et Déblocage

**Tables Utilisées** :
- `prets` : Mise à jour du statut
- `amortissements` : Génération du plan
- `transactions` : Déblocage des fonds

**Actions** :
- Génération du plan d'amortissement
- Déblocage vers compte client
- Mise à jour du statut à "Approuvé"

### 3.3 Suivi des Remboursements

**Tables Utilisées** :
- `amortissements` : Suivi des échéances
- `transactions` : Enregistrement des paiements
- `soldes` : Mise à jour des soldes

**Fonctionnalités** :
- Marquage des échéances payées
- Détection des retards
- Calcul du capital restant dû

---

## 4. Fonctions Spécialisées

### 4.1 Calcul des Mensualités

**Méthodes selon `type_amortissement`** :
- **CONSTANT** : Capital fixe + intérêts dégressifs
- **MENSALITES_FIXES** : Formule classique
- **NON_AMORTISSABLE** : Intérêts seuls
- **MODULABLE** : Adaptable selon capacité client

### 4.2 Reporting

**Données Utilisées** :
- Historique des `transactions`
- État des `prets` et `amortissements`
- Évolution des `soldes`

**Indicateurs** :
- Taux de défaut
- Portefeuille de prêts
- Liquidité de l'établissement

<!-- **Formule CONSTANT:**
- Amortissement mensuel = Capital / Nombre de mois

- Intérêts du mois = Capital restant × (Taux annuel / 12)

- Mensualité = Amortissement + Intérêts

🔹 Exemple :
- Capital = 1 200 000 Ar

- Durée = 12 mois

- Taux annuel = 12 % → Taux mensuel = 1 % = 0,01

 -->



