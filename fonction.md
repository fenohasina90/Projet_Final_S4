# Fonctionnalités Détaillées du Système Bancaire

## 1. Ajout de Fonds dans l'Établissement Financier

**Description** :  
Permet d'enregistrer les entrées d'argent (dépôts clients, virements, etc.) dans les comptes de l'établissement.

**Tables Utilisées** :
- `comptes` : Vérification du compte crédité
- `soldes` : Mise à jour du solde
- `transactions` : Journalisation de l'opération

**Workflow** :
1. Vérification de l'existence du compte (via `comptes.iban`)
2. Création d'une entrée dans `transactions` (type 'Dépôt')
3. Mise à jour du `soldes` avec le nouveau montant

---

## 2. Création de Types de Prêt avec Différents Taux

**Description** :  
Configuration des différents produits de crédit proposés par la banque.

**Tables Utilisées** :
- `types_pret` : Stockage des caractéristiques du prêt
- `taux_interet` : Référence des taux applicables

**Paramètres Configurables** :
- Taux annuel (lié à `taux_interet`)
- Durée maximale
- Plage de montants
- Type d'amortissement (4 options)

---

## 3. Gestion des Prêts Clients

### 3.1 Demande de Prêt

**Tables Utilisées** :
- `clients` : Vérification du score crédit
- `prets` : Enregistrement de la demande
- `types_pret` : Récupération des conditions

**Processus** :
1. Vérification éligibilité client (`clients.score_credit`)
2. Calcul de la mensualité selon `types_pret.type_amortissement`
3. Création enregistrement dans `prets`

### 3.2 Suivi des Remboursements

**Tables Utilisées** :
- `amortissements` : Échéancier détaillé
- `transactions` : Trace des paiements
- `soldes` : Impact sur le compte

**Fonctionnalités** :
- Visualisation du calendrier de remboursement
- Détection automatique des retards
- Historique complet des transactions

### 3.3 Gestion des Amortissements

**Tables Utilisées** :
- `amortissements` : Plan de remboursement
- `prets` : Référence du contrat

**Types Supportés** :
- Amortissement constant (mensualités décroissantes)
- Mensualités fixes
- Prêts non amortissables (in fine)
- Plans modulables

---

## Tables Clés par Fonctionnalité

| Fonctionnalité               | Tables Principales                 | Tables Secondaires       |
|------------------------------|------------------------------------|--------------------------|
| Ajout de fonds               | transactions, soldes              | comptes                 |
| Configuration produits       | types_pret, taux_interet          | -                       |
| Gestion demande de prêt      | prets, clients                    | types_pret              |
| Suivi remboursements         | amortissements, transactions      | prets, soldes           |
| Calcul d'amortissement       | amortissements, prets             | types_pret              |

**Relations Importantes** :
- Un client (`clients`) peut avoir plusieurs comptes (`comptes`) et prêts (`prets`)
- Chaque prêt génère un plan d'amortissement (`amortissements`)
- Toutes les opérations financières sont tracées (`transactions`)