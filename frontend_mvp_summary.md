# 🎨 FRONTEND MVP - Résumé des Changements

## 📌 Différences MVP vs Version Complète

### ❌ Fonctionnalités RETIRÉES du MVP

1. **Système de Divisions**
   - Pas de saisons
   - Pas de promotion/relégation
   - Pas de divisions D1/D2/D3/D4

2. **MLM Rank (ELO)**
   - Pas de classement global
   - Pas de Hall of Fame
   - Statistiques simplifiées

3. **Chat & Communication**
   - Pas de chat intégré dans les tournois
   - Pas de notifications push temps réel

4. **Système d'Arbitrage**
   - Pas de rôle Arbitre
   - Pas de litiges complexes
   - Les organisateurs gèrent directement les contestations

5. **Badges Organisateur à Niveaux**
   - Pas de système de niveaux 0/1/2/3
   - Organisateur = rôle simple

6. **Recharge/Retrait de Fonds**
   - Pas de recharge de pièces MLM
   - Pas de retrait vers Mobile Money
   - Utilisation uniquement des 10 pièces gratuites

---

## ✅ Fonctionnalités CONSERVÉES dans le MVP

### 1. **Authentification & Rôles**
- Inscription / Connexion
- 4 rôles : Admin, Modérateur, Organisateur, Joueur

### 2. **Profil Joueur Complet**
- Informations personnelles (Nom, WhatsApp, Pays, Ville)
- Multi-sélection de jeux (E-football, FC Mobile, DLS)
- Pour chaque jeu : Pseudo + Screenshot d'équipe
- **Workflow de validation par Modérateur**

### 3. **Système de Pièces Simplifié**
- 10 pièces offertes après validation du profil (1 pièce = 500 FCFA)
- Affichage du solde
- Déduction lors de l'inscription à un tournoi
- Gains automatiques pour les gagnants

### 4. **Tournois Format Suisse**
- Création de tournois par Organisateurs
- Inscription avec paiement en pièces MLM
- Calcul automatique du nombre de tours : N = ⌈log₂(P)⌉
- Génération d'appariements (joueurs avec même score s'affrontent)
- Gestion des rondes
- Saisie des résultats avec screenshots
- Classement du tournoi
- Distribution automatique des gains

### 5. **Dashboard & Navigation**
- Dashboard Joueur
- Dashboard Organisateur
- Dashboard Modérateur
- Dashboard Admin

---

## 🗺️ Pages Principales du MVP

### Pages Publiques
- `/` - Page d'accueil
- `/login` - Connexion
- `/register` - Inscription
- `/tournaments` - Liste des tournois publics

### Pages Joueur (après connexion)
- `/home` - Dashboard joueur
- `/profile` - Mon profil
- `/game-profiles` - Mes infos de jeu (Pseudos, screenshots)
- `/tournaments` - Liste des tournois
- `/tournaments/:id` - Détails d'un tournoi
- `/my-matches` - Mes matchs
- `/matches/:id/submit` - Soumettre résultat
- `/history` - Mon historique
- `/become-organizer` - Devenir organisateur
- `/settings` - Paramètres

### Pages Organisateur
- `/organizer/dashboard` - Dashboard organisateur
- `/organizer/tournaments` - Mes tournois
- `/organizer/create-tournament` - Créer un tournoi
- `/organizer/tournaments/:id` - Gérer un tournoi

### Pages Modérateur
- `/moderator/dashboard` - Dashboard modération
- `/moderator/profile-validations` - Valider les profils joueurs

### Pages Admin
- `/admin/dashboard` - Dashboard admin
- `/admin/users` - Gestion utilisateurs
- `/admin/tournaments` - Tous les tournois
- `/admin/finances` - Finances (soldes, distributions)

---

## 🔄 Workflow Clés du MVP

### 1. Inscription & Validation Profil
```
1. Utilisateur s'inscrit (email, mot de passe, pays, ville, WhatsApp)
2. Utilisateur complète son profil de jeu :
   - Sélectionne les jeux qu'il joue (E-football, FC Mobile, DLS)
   - Pour chaque jeu : Pseudo + Upload screenshot d'équipe
3. Statut du profil : "En attente de validation"
4. Modérateur valide le profil
5. Système attribue automatiquement 10 pièces MLM
6. Utilisateur peut maintenant s'inscrire aux tournois
```

### 2. Inscription à un Tournoi
```
1. Joueur consulte la liste des tournois disponibles
2. Clic sur un tournoi → Voir détails
3. Vérification : Profil validé + Solde suffisant
4. Clic sur [S'inscrire]
5. Confirmation : Déduction de X pièces
6. Joueur ajouté à la liste des participants
7. Quand le tournoi commence → Génération des appariements
```

### 3. Création de Tournoi (Organisateur)
```
1. Organisateur clique sur [Créer Tournoi]
2. Formulaire :
   - Nom du tournoi
   - Jeu (E-football / FC Mobile / DLS)
   - Date de début
   - Nombre max de participants
   - Format : Suisse
   - Frais d'inscription (en pièces MLM)
   - Distribution des gains (1er, 2e, 3e...)
3. Clic sur [Publier]
4. Tournoi visible dans la liste publique
5. Joueurs peuvent s'inscrire
6. Au début → Organisateur génère les appariements
```

### 4. Déroulement d'un Match
```
1. Système génère les appariements pour la ronde
2. Joueurs voient leur match dans "Mes Matchs"
3. Les 2 joueurs jouent leur match (hors plateforme)
4. Chaque joueur soumet son résultat :
   - Score de son équipe
   - Score adversaire
   - Upload screenshot
5. Si les 2 résultats concordent → Match validé automatiquement
6. Si résultats différents → Organisateur tranche
7. Passage à la ronde suivante
```

### 5. Fin de Tournoi
```
1. Toutes les rondes sont terminées
2. Système calcule le classement final (basé sur les points)
3. Distribution automatique des gains :
   - 1er place : X pièces
   - 2e place : Y pièces
   - 3e place : Z pièces
4. Gains ajoutés aux soldes des gagnants
5. Tournoi marqué comme "Terminé"
```

---

## 📋 Formulaires Principaux du MVP

### Formulaire Inscription
- Nom complet
- Email
- Mot de passe
- Pays
- Ville
- Numéro WhatsApp
- [Créer mon compte]

### Formulaire Compléter Profil de Jeu
- Multi-select : Jeux pratiqués (E-football, FC Mobile, DLS)
- **Pour chaque jeu sélectionné** :
  - Pseudo dans le jeu (input)
  - Screenshot de l'équipe (upload)
- [Envoyer pour validation]

### Formulaire Créer Tournoi
- Nom du tournoi
- Jeu (select)
- Description
- Date de début
- Nombre max de participants
- Frais d'inscription (en pièces MLM)
- Distribution des gains :
  - 1ère place : X%
  - 2ème place : Y%
  - 3ème place : Z%
- [Publier]

### Formulaire Soumettre Résultat
- Mon score (input number)
- Score adversaire (input number)
- Screenshot du résultat (upload)
- Commentaire optionnel
- [Soumettre]

---

## 🎯 Composants Réutilisables MVP

### TournamentCard
- Affiche un tournoi (nom, jeu, date, participants, frais)
- Bouton : [Voir détails] ou [S'inscrire]

### MatchCard
- Affiche un match (Joueur A vs Joueur B, date, statut)
- Bouton : [Soumettre résultat] ou [Voir détails]

### UserBadge
- Avatar + rôle (Modérateur, Organisateur, Admin)

### BalanceDisplay
- Affichage du solde en pièces MLM
- "X pièces (= Y FCFA)"

### StatusBadge
- Badge de statut :
  - Profil : Validé / En attente / Refusé
  - Tournoi : Inscriptions ouvertes / En cours / Terminé
  - Match : À jouer / En attente / Validé / Contesté

### SwissBracket
- Affichage du bracket Format Suisse
- Liste des rondes avec appariements
- Scores et résultats

---

## 🔔 Notifications Simples (MVP)

**Toast/Snackbar uniquement** :
- ✅ "Profil envoyé pour validation"
- ✅ "Inscription au tournoi réussie"
- ✅ "Résultat soumis avec succès"
- ✅ "Votre profil a été validé ! 10 pièces ajoutées à votre compte"
- ❌ "Solde insuffisant"
- ❌ "Échec de la soumission"

**Pas de notifications push dans le MVP** → Phase 2

---

## 📱 Responsive Design MVP

### Mobile First
- Navigation hamburger sur mobile
- Cards en 1 colonne
- Formulaires full-width
- Boutons tactiles (min 44px)

### Desktop
- Sidebar navigation
- Cards en grille (2-3 colonnes)
- Tableaux complets

---

## 🚀 Prochaines Étapes Développement

### Phase 1.1 : Auth & Profils
1. Pages d'inscription/connexion
2. Compléter profil de jeu
3. Validation par modérateur
4. Attribution des 10 pièces

### Phase 1.2 : Tournois
1. Liste des tournois
2. Détails d'un tournoi
3. Création de tournoi (organisateur)
4. Inscription à un tournoi

### Phase 1.3 : Format Suisse
1. Calcul du nombre de tours
2. Génération des appariements
3. Gestion des rondes
4. Classement final

### Phase 1.4 : Matchs
1. Soumettre résultat
2. Validation automatique
3. Gestion des contestations (simple)
4. Historique

### Phase 1.5 : Wallet
1. Affichage du solde
2. Déduction lors inscription
3. Distribution des gains
4. Historique des transactions

### Phase 1.6 : Admin & Modération
1. Dashboard admin
2. Gestion utilisateurs
3. Validation des profils
4. Vue d'ensemble des tournois

---

## 📊 Métriques à Tracker (MVP)

- Nombre d'inscrits
- Nombre de profils en attente de validation
- Nombre de tournois actifs
- Nombre de matchs joués
- Distribution des pièces (données, pas retirait)

---

**Fin du Document**
