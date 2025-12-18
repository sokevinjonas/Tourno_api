# 🎨 FRONTEND ANGULAR - MOBILE LEAGUE MANAGER (MLM)

## Spécifications de l'Interface Utilisateur - MVP

**Version** : 1.0 MVP
**Date** : Décembre 2024
**Framework** : Angular 17+ (Standalone Components)
**Type** : Application Web Progressive (PWA) - Desktop & Mobile Responsive

**Note** : Ce document décrit l'interface pour le MVP (Minimum Viable Product). Les fonctionnalités avancées (Divisions, ELO Rank, Chat, Arbitrage, etc.) sont reportées en Phase 2.

---

## 📋 TABLE DES MATIÈRES (MVP)

1. [Architecture de Navigation](#architecture-de-navigation)
2. [Pages Publiques (Non Connecté)](#pages-publiques-non-connecté)
3. [Pages Joueur](#pages-joueur)
4. [Pages Organisateur](#pages-organisateur)
5. [Pages Modérateur](#pages-modérateur)
6. [Pages Administrateur](#pages-administrateur)
7. [Formulaires Détaillés](#formulaires-détaillés)
8. [Composants Réutilisables](#composants-réutilisables)
9. [Modals & Popups](#modals--popups)
10. [Responsive Design](#responsive-design)
11. [Fonctionnalités Phase 2](#fonctionnalités-phase-2)

---

## 🗺️ Architecture de Navigation

### Menu Principal (Header)

#### **Pour Utilisateur Non Connecté**

```
┌────────────────────────────────────────────────────────────┐
│  [LOGO MLM]    Accueil   Tournois   Se connecter   S'inscrire │
└────────────────────────────────────────────────────────────┘
```

**Menu Items** :
- **Accueil** → `/` (Page d'accueil publique)
- **Tournois** → `/tournaments` (Liste des tournois publics)
- **Se connecter** → `/login` (Modal ou page de connexion)
- **S'inscrire** → `/register` (Modal ou page d'inscription)

---

#### **Pour Utilisateur Connecté (Joueur)**

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  [LOGO MLM]  Accueil  Tournois  Mes Matchs  [Solde: X pièces] [Avatar ▼]     │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Menu Items** :
- **Accueil** → `/home` (Dashboard joueur)
- **Tournois** → `/tournaments` (Liste des tournois disponibles)
- **Mes Matchs** → `/my-matches` (Matchs à jouer et historique)
- **Solde MLM** → Affichage du nombre de pièces disponibles
- **Avatar Dropdown** :
  - Mon Profil → `/profile`
  - Mes Infos de Jeu → `/game-profiles` (Pseudos et screenshots par jeu)
  - Mon Historique → `/history`
  - Devenir Organisateur → `/become-organizer`
  - Paramètres → `/settings`
  - Se déconnecter

---

#### **Pour Organisateur**

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  [LOGO MLM]  Accueil  Mes Tournois  Créer Tournoi  [Solde: X pièces] [Avatar ▼]  │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

**Menu Items supplémentaires** :
- **Mes Tournois** → `/organizer/tournaments` (Liste des tournois créés)
- **Créer Tournoi** → `/organizer/create-tournament` (Formulaire création)
- **Solde MLM** → Affichage du nombre de pièces disponibles

---

#### **Pour Modérateur**

```
┌────────────────────────────────────────────────────────────────────────────────┐
│  [LOGO MLM]  Accueil  Validations Profils  [🛡️] [Avatar ▼] │
└────────────────────────────────────────────────────────────────────────────────┘
```

**Menu Items supplémentaires** :
- **Accueil** → `/moderator/dashboard` (Dashboard modération)
- **Validations Profils** → `/moderator/profile-validations` (Validation des profils joueurs)
- **🛡️ Badge Modérateur** → Visible à côté de l'avatar

---

#### **Pour Administrateur**

```
┌───────────────────────────────────────────────────────────────────────────────────┐
│  [LOGO MLM]  Dashboard  Utilisateurs  Tournois  Finances  Stats  [👑] [Avatar ▼] │
└───────────────────────────────────────────────────────────────────────────────────┘
```

**Menu Items supplémentaires** :
- **Dashboard Admin** → `/admin/dashboard` (Vue d'ensemble plateforme)
- **Utilisateurs** → `/admin/users` (Gestion utilisateurs)
- **Tournois** → `/admin/tournaments` (Supervision tous tournois)
- **Finances** → `/admin/finances` (Revenus, commissions, paiements)
- **Stats Globales** → `/admin/stats` (Analytics plateforme)
- **👑 Badge Admin** → Visible à côté de l'avatar

---

## 🌍 Pages Publiques (Non Connecté)

### 1. **Page d'Accueil Publique** (`/`)

**Sections** :
- **Hero Section** :
  - Titre : "Mobile League Manager - La Plateforme de Tournois E-sports Mobile en Afrique"
  - Sous-titre : "Organise et participe à des tournois de jeux mobiles. Gagne de l'argent réel."
  - CTA : [S'inscrire] [Se connecter]
  - Image/Vidéo : Illustration de tournoi

- **Comment ça marche** (3 étapes) :
  1. Crée ton compte → Ajoute tes pseudos de jeu
  2. Inscris-toi à des tournois → Gratuits ou payants
  3. Joue et gagne → Prize pools en FCFA

- **Système de Divisions** :
  - Explication rapide (D1 → D4)
  - Lien vers page `/divisions`

- **Statistiques de la plateforme** :
  - Nombre de joueurs inscrits
  - Nombre de tournois organisés
  - Total des prize pools distribués (en FCFA)

- **Témoignages** (optionnel)

- **Footer** :
  - Liens : À propos, Contact, CGU, Politique de confidentialité
  - Réseaux sociaux

---

### 2. **Page Connexion** (`/login`)

**Méthodes d'authentification** :

#### **Option 1 : OAuth Social (Recommandé)**
- Bouton : [🔵 Continuer avec Google]
- Bouton : [⚫ Continuer avec Apple]
- Bouton : [🔵 Continuer avec Facebook]

#### **Option 2 : Magic Link (Email)**
- Séparateur : "ou"
- Champ : Email (input type="email")
- Bouton : [📧 Recevoir un lien de connexion]
- Message : "Nous vous enverrons un lien de connexion par email"

**Après soumission (Magic Link)** :
- Message de confirmation : "✉️ Email envoyé ! Vérifiez votre boîte de réception"
- Instructions : "Cliquez sur le lien dans l'email pour vous connecter. Le lien expire dans 15 minutes."

**Page de vérification** (`/auth/verify?token=XXXXX`)
- Affichage d'un loader : "Vérification en cours..."
- Si succès : Redirection automatique vers `/home` ou `/profile/complete`
- Si échec : "❌ Lien invalide ou expiré. [Renvoyer un email]"

---

### 3. **Page Inscription** (`/register`)

**Note MVP** : Pas de page d'inscription séparée. L'inscription se fait automatiquement via l'authentification OAuth ou Magic Link.

**Workflow** :
1. Utilisateur clique sur "Continuer avec Google" (ou autre)
2. Si premier connexion → Compte créé automatiquement
3. Redirection vers `/profile/complete` pour compléter le profil

---

### 4. **Page Divisions** (`/divisions`)

**Contenu** :
- Explication du système de divisions (D1, D2, D3, D4)
- Calendrier des saisons (3 mois)
- Système de qualification
- Frais d'inscription par saison
- Distribution des prize pools
- Système de bannissement

---

### 5. **Page Classements Publics** (`/rankings`)

**Sections** :
- **Top 100 MLM Rank** (classement ELO global)
  - Tableau : Rang, Pseudo, Jeu, Points ELO, Victoires
- **Hall of Fame** (Champions des saisons passées)
  - Filtre par saison
  - Filtre par division (D1/D2/D3/D4)

---

## 🏠 Pages Utilisateur Standard

### 1. **Dashboard Utilisateur** (`/home`)

**Sections** :
- **Carte de Bienvenue** : "Bonjour [Prénom] !"
- **Mes Prochains Matchs** (liste des 3 prochains matchs programmés)
  - Tournoi, Adversaire, Date/Heure, Bouton [Voir]
- **Tournois Disponibles** (liste des 5 tournois ouverts aux inscriptions)
  - Nom, Jeu, Date, Frais, Nombre de places, Bouton [S'inscrire]
- **Mes Stats Rapides** :
  - MLM Rank actuel
  - Victoires / Défaites
  - Division actuelle (si inscrit à la saison)
- **Notifications récentes** (3 dernières)

---

### 2. **Page Tournois** (`/tournaments`)

**Filtres** :
- Jeu (E-football, FC Mobile, Dream League Soccer)
- Type (Gratuit / Payant)
- Format (Bracket, Groupes, Élimination directe)
- Date (Aujourd'hui, Cette semaine, Ce mois)
- Statut (Inscriptions ouvertes, En cours, Terminé)

**Liste des Tournois** (Cards) :
- Nom du tournoi
- Jeu (icon)
- Organisateur (nom + badge si certifié)
- Date de début
- Frais d'inscription (0 MLC ou X MLC)
- Nombre de participants (ex: 12/32)
- Prize pool (si payant)
- Bouton : [Voir détails] ou [S'inscrire]

---

### 3. **Page Détails Tournoi** (`/tournaments/:id`)

**Sections** :
- **En-tête** :
  - Nom du tournoi
  - Badge (Privé/Public)
  - Organisateur (nom + badge + note)
  - Date/Heure
  - Jeu

- **Informations** :
  - Description
  - Format (bracket, groupes)
  - Nombre de participants (ex: 16/32)
  - Frais d'inscription
  - Prize pool et distribution
  - Règles spécifiques

- **Onglets** :
  - **Participants** : Liste des inscrits avec pseudos
  - **Bracket/Groupes** : Visualisation du bracket ou des groupes (si tournoi commencé)
  - **Résultats** : Matchs joués avec scores (si tournoi en cours/terminé)
  - **Chat** : Discussion entre participants

- **Actions** :
  - Bouton : [S'inscrire] (si places disponibles)
  - Bouton : [Se désinscrire] (si déjà inscrit et avant début)

---

### 4. **Page Mes Matchs** (`/my-matches`)

**Onglets** :
- **À Jouer** : Matchs programmés pas encore joués
- **En Attente de Résultat** : Matchs joués mais résultat non soumis
- **Terminés** : Historique des matchs

**Tableau** :
- Tournoi
- Adversaire
- Date/Heure
- Statut (À jouer, En attente, Validé, Contesté)
- Action : [Soumettre résultat] ou [Voir détails]

---

### 5. **Page Soumettre Résultat Match** (`/matches/:id/submit`)

**Formulaire** :
- Score de votre équipe (input number)
- Score adversaire (input number)
- Upload screenshot (file input)
  - Prévisualisation de l'image uploadée
- Commentaire optionnel (textarea)
- Bouton : [Soumettre le résultat]

**Informations affichées** :
- Nom du tournoi
- Votre pseudo vs Pseudo adversaire
- Date/Heure du match

---

### 6. **Page Mes Équipes** (`/my-teams`)

**Section** :
- **E-football** :
  - Équipe 1 : "KarimPro" (principale) - [Modifier] [Supprimer]
  - Équipe 2 : "KarimElite" (secondaire) - [Modifier] [Supprimer]
  - [+ Ajouter une équipe] (si < 2 équipes)

- **FC Mobile** :
  - [+ Ajouter une équipe]

- **Dream League Soccer** :
  - [+ Ajouter une équipe]

**Formulaire Ajouter/Modifier Équipe** (Modal) :
- Nom de l'équipe / Pseudo dans le jeu (input)
- Type (Principale / Secondaire) - (radio buttons)
- Bouton : [Enregistrer]

---

### 7. **Page Profil Utilisateur** (`/profile`)

**Sections** :
- **Informations Personnelles** :
  - Photo de profil (upload)
  - Nom complet
  - Email
  - Pays
  - Numéro de téléphone
  - Bouton : [Modifier]

- **Statistiques** :
  - MLM Rank : 1250 points
  - Victoires : 45
  - Défaites : 23
  - Nuls : 5
  - Taux de victoire : 62%
  - Tournois joués : 12
  - Tournois gagnés : 3

- **Historique des Divisions** :
  - Saison 1 (Juillet-Sept 2024) : D2 - 3ème place
  - Saison 2 (Oct-Déc 2024) : D3 - 5ème place

- **Badges et Réalisations** :
  - Champion D2 Saison 1
  - Top 100 MLM Rank
  - 50+ Victoires

---

### 8. **Page Divisions & Saisons** (`/divisions`)

**Sections** :
- **Saison en Cours** :
  - Nom : Saison 3 (Janvier-Mars 2025)
  - Statut : Inscriptions ouvertes / En cours / Terminée
  - Frais d'inscription : 8 MLC
  - Date de début : 1er Janvier 2025
  - Date de fin : 31 Mars 2025
  - Inscrits : 85 / 120
  - Bouton : [S'inscrire]

- **Mes Inscriptions** :
  - Vous êtes inscrit en tant que "KarimPro"
  - Division actuelle : D3 (Excellence)
  - Classement du groupe : 2ème / 6
  - Prochaine journée : Mercredi 15/01 à 20h

- **Historique des Saisons** :
  - Tableau : Saison, Division, Classement, Récompenses

- **Explication du Système** :
  - Comment fonctionnent les divisions
  - Système de qualification
  - Règles de bannissement

---

### 9. **Page Historique** (`/history`)

**Onglets** :
- **Tournois** : Liste de tous les tournois joués avec résultats
- **Matchs** : Historique de tous les matchs
- **Transactions** : Historique des paiements (inscriptions, gains)

---

### 10. **Page Devenir Organisateur** (`/become-organizer`)

**Contenu** :
- **Introduction** : Pourquoi devenir organisateur ?
- **Avantages** :
  - Gagner des commissions (50-55%)
  - Construire une communauté
  - Badges et visibilité

- **Système de Badges** (tableau) :
  - Niveau 0 : Gratuit, tournois gratuits uniquement
  - Niveau 1 : 50,000 FCFA, tournois payants
  - Niveau 2 : Automatique après 1 tournoi réussi
  - Niveau 3 : Automatique après 5 tournois réussis

- **Formulaire de Demande** (pour Niveau 1) :
  - Upload carte d'identité
  - Upload selfie avec carte
  - Acceptation des conditions
  - Paiement 50,000 FCFA via Mobile Money
  - Bouton : [Soumettre ma demande]

---

### 11. **Page Paramètres** (`/settings`)

**Onglets** :
- **Compte** :
  - Modifier email
  - Modifier mot de passe
  - Modifier numéro de téléphone

- **Notifications** :
  - Recevoir emails (on/off)
  - Recevoir SMS (on/off)
  - Notifications de match (on/off)
  - Notifications de tournoi (on/off)

- **Confidentialité** :
  - Profil public/privé
  - Afficher mon MLM Rank publiquement

- **Sécurité** :
  - Activer 2FA (Two-Factor Authentication)
  - Historique des connexions

- **Danger Zone** :
  - Supprimer mon compte

---

## 🏆 Pages Organisateur

### 1. **Dashboard Organisateur** (`/organizer/dashboard`)

**Sections** :
- **Carte Badge** :
  - Badge actuel (Niveau 0/1/2/3)
  - Progression vers niveau suivant (si applicable)
  - Conditions pour niveau suivant

- **Mes Stats** :
  - Tournois organisés : 7
  - Participants totaux : 342
  - Revenus générés : 175,000 FCFA
  - Note moyenne : 4.6/5

- **Mes Tournois Actifs** :
  - Liste des tournois en cours ou à venir (cards)

- **Actions Rapides** :
  - [Créer un nouveau tournoi]
  - [Voir tous mes tournois]
  - [Mes revenus]

---

### 2. **Page Mes Tournois** (`/organizer/tournaments`)

**Filtres** :
- Statut (Brouillon, Inscriptions ouvertes, En cours, Terminé)
- Jeu
- Type (Gratuit/Payant)

**Liste des Tournois** (Tableau) :
- Nom
- Jeu
- Date
- Participants (ex: 12/32)
- Statut
- Revenus (si payant)
- Actions : [Voir] [Modifier] [Supprimer]

---

### 3. **Page Créer Tournoi** (`/organizer/create-tournament`)

**Formulaire (Multi-étapes)** :

**Étape 1 : Informations Générales**
- Nom du tournoi (input)
- Jeu (select : E-football, FC Mobile, Dream League Soccer)
- Description (textarea)
- Date de début (date picker)
- Heure de début (time picker)

**Étape 2 : Configuration**
- Type de tournoi (radio) :
  - Gratuit (seulement si niveau 0-3)
  - Payant (seulement si niveau 1+)
- Frais d'inscription (input number si payant)
- Nombre maximum de participants (input number)
  - Limite selon badge : 100 (niveau 0), 200 (niveau 1), 500 (niveau 2), Illimité (niveau 3)
- Format (select) :
  - Bracket simple élimination
  - Double élimination
  - Groupes puis élimination
  - Round-robin (tous contre tous)

**Étape 3 : Accessibilité**
- Visibilité (radio) :
  - Public (tout le monde peut s'inscrire)
  - Privé (code d'invitation requis)
- Code d'invitation (input si privé) - généré automatiquement ou personnalisé

**Étape 4 : Prize Pool** (si payant)
- Distribution automatique (selon nombre de participants)
- Ou personnalisée :
  - 1ère place : X MLC
  - 2ème place : X MLC
  - 3ème place : X MLC
  - Etc.

**Étape 5 : Règles**
- Règles spécifiques (textarea)
- Temps limite pour soumettre résultats : 24h (par défaut)
- Autoriser arbitrage (on/off - on si niveau 1+)

**Boutons** :
- [Précédent] [Suivant]
- [Enregistrer comme brouillon]
- [Publier le tournoi] (dernière étape)

---

### 4. **Page Détails Tournoi Organisateur** (`/organizer/tournaments/:id`)

**Onglets** :
- **Vue d'ensemble** :
  - Informations du tournoi
  - Statistiques (inscrits, vues, taux de remplissage)
  - Actions : [Modifier] [Annuler tournoi] [Clôturer inscriptions]

- **Participants** :
  - Liste des inscrits avec contacts
  - [Envoyer un message groupé]
  - [Exporter liste (CSV)]

- **Bracket/Groupes** :
  - Génération automatique du bracket (bouton)
  - Modification manuelle possible
  - Statut de chaque match

- **Matchs** :
  - Liste de tous les matchs avec statuts
  - Actions : [Valider résultat] [Contester]

- **Prize Pool** :
  - Montant total collecté
  - Distribution prévue
  - Statut des paiements (En attente / Distribué)
  - [Distribuer les gains] (bouton)

- **Chat** :
  - Discussion avec participants
  - Annonces de l'organisateur

---

### 5. **Page Stats Organisateur** (`/organizer/stats`)

**Sections** :
- **Performance Globale** :
  - Graphique : Nombre de tournois par mois
  - Graphique : Participants par tournoi (moyenne)
  - Graphique : Revenus mensuels

- **Réputation** :
  - Note moyenne : 4.6/5 (basée sur X avis)
  - Taux de satisfaction participants
  - Commentaires récents

- **Finances** :
  - Revenus totaux : 175,000 FCFA
  - Commissions MLM (15%) : 26,250 FCFA
  - Gains nets : 148,750 FCFA
  - Paiements en attente : 15,000 FCFA

---

### 6. **Page Demande de Certification** (`/organizer/certification`)

**Formulaire** (si niveau 0 → niveau 1) :
- Upload carte d'identité (CNI ou passeport)
- Upload selfie avec carte
- Confirmation numéro de téléphone Mobile Money
- [J'accepte les conditions de certification] (checkbox)
- Bouton : [Payer 50,000 FCFA et soumettre]

**Statut de la demande** :
- En attente de paiement
- Paiement reçu - En cours de vérification
- Approuvée ✅
- Refusée ❌ (avec raison)

---

## 🛡️ Pages Modérateur

### 1. **Dashboard Modération** (`/moderator/dashboard`)

**Sections** :
- **File d'attente** :
  - Plaintes en attente : 12
  - Demandes de certification en attente : 5
  - Signalements de spam : 8

- **Mes Stats** :
  - Plaintes traitées ce mois : 45
  - Certifications validées : 8
  - Bannissements effectués : 3

- **Alertes** :
  - Utilisateurs signalés 3+ fois
  - Organisateurs avec note < 3/5

---

### 2. **Page Plaintes** (`/moderator/complaints`)

**Filtres** :
- Statut (Nouvelle, En traitement, Résolue, Rejetée)
- Type (Spam, Harcèlement, Triche, Arnaque, Autre)
- Priorité (Haute, Moyenne, Basse)

**Liste des Plaintes** (Tableau) :
- ID
- Utilisateur plaignant
- Utilisateur accusé
- Type
- Date
- Priorité
- Statut
- Action : [Traiter]

---

### 3. **Page Détails Plainte** (`/moderator/complaints/:id`)

**Sections** :
- **Informations** :
  - Plaignant : [Pseudo] (lien vers profil)
  - Accusé : [Pseudo] (lien vers profil)
  - Type de plainte
  - Date de soumission
  - Description détaillée

- **Preuves** :
  - Screenshots uploadés
  - Historique des messages (si plainte sur chat)

- **Historique de l'accusé** :
  - Nombre de plaintes reçues
  - Bannissements antérieurs

- **Actions** :
  - [Envoyer avertissement]
  - [Bannir temporairement] (durée : 7j, 30j)
  - [Bannir définitivement]
  - [Rejeter la plainte]
  - [Demander plus d'informations]

- **Commentaire du modérateur** (textarea)
- Bouton : [Soumettre la décision]

---

### 4. **Page Validations Organisateurs** (`/moderator/validations`)

**Liste des Demandes** (Cards) :
- Nom de l'utilisateur
- Email
- Pays
- Date de demande
- Documents uploadés (liens pour télécharger)
- Statut du paiement (50,000 FCFA)
- Actions : [Approuver] [Rejeter] [Voir détails]

---

### 5. **Page Détails Demande Certification** (`/moderator/validations/:id`)

**Sections** :
- **Informations Utilisateur** :
  - Nom complet
  - Email
  - Pays
  - Numéro de téléphone
  - Historique sur MLM (tournois joués, note si a participé)

- **Documents** :
  - Carte d'identité (image, bouton pour agrandir)
  - Selfie avec carte (image)
  - Preuve de paiement Mobile Money

- **Vérification** :
  - Checklist :
    - [ ] Carte d'identité valide et lisible
    - [ ] Selfie correspond à la carte
    - [ ] Paiement de 50,000 FCFA reçu
    - [ ] Pas de bannissement dans l'historique
    - [ ] Profil complété correctement

- **Décision** :
  - [Approuver] → Badge Niveau 1 attribué
  - [Rejeter] → Indiquer raison (textarea)

---

## ⚖️ Pages Arbitre

### 1. **Dashboard Arbitre** (`/arbitre/dashboard`)

**Sections** :
- **File d'attente Litiges** :
  - En attente : 7 litiges
  - En cours de traitement : 2
  - Résolus aujourd'hui : 5

- **Mes Stats** :
  - Litiges résolus ce mois : 34
  - Temps moyen de résolution : 18h
  - Taux de satisfaction : 92%
  - Revenus ce mois : 17,000 FCFA (20k fixe + bonus)

---

### 2. **Page Litiges** (`/arbitre/disputes`)

**Filtres** :
- Statut (Nouveau, Assigné à moi, En traitement, Résolu)
- Priorité (Haute, Moyenne)
- Jeu

**Liste des Litiges** (Cards) :
- ID du litige
- Tournoi
- Match : [Joueur A] vs [Joueur B]
- Date du litige
- Priorité
- Statut
- Actions : [Prendre en charge] [Voir détails]

---

### 3. **Page Détails Litige** (`/arbitre/disputes/:id`)

**Sections** :
- **Informations Match** :
  - Tournoi : [Nom]
  - Organisateur : [Nom + Badge]
  - Joueur A : [Pseudo]
  - Joueur B : [Pseudo]
  - Date du match

- **Résultats Soumis** :
  - **Joueur A** :
    - Score déclaré : 3-2
    - Screenshot uploadé (image)
    - Commentaire : "..."
  - **Joueur B** :
    - Score déclaré : 2-3
    - Screenshot uploadé (image)
    - Commentaire : "..."

- **Historique des Joueurs** :
  - Joueur A : X matchs joués, Y litiges passés
  - Joueur B : X matchs joués, Y litiges passés

- **Preuves Supplémentaires** :
  - Messages entre les 2 joueurs (chat)
  - Vidéos uploadées (si disponibles)

- **Décision de l'Arbitre** :
  - Radio buttons :
    - [ ] Valider résultat Joueur A (3-2)
    - [ ] Valider résultat Joueur B (2-3)
    - [ ] Déclarer match nul (preuves insuffisantes)
    - [ ] Sanctionner Joueur A (triche avérée)
    - [ ] Sanctionner Joueur B (triche avérée)
  - Justification (textarea obligatoire)
  - Bouton : [Soumettre la décision]

---

### 4. **Page Historique Arbitre** (`/arbitre/history`)

**Liste des Décisions Passées** (Tableau) :
- ID
- Tournoi
- Match
- Date de résolution
- Décision
- Satisfaction (si évalué par les joueurs)
- [Voir détails]

---

## 👑 Pages Administrateur

### 1. **Dashboard Admin** (`/admin/dashboard`)

**Widgets** :
- **Utilisateurs** :
  - Total utilisateurs : 15,234
  - Nouveaux ce mois : 1,245
  - Utilisateurs actifs (7j) : 8,432

- **Tournois** :
  - Total tournois : 3,456
  - Tournois actifs : 23
  - Tournois ce mois : 234

- **Finances** :
  - Revenus totaux : 12,450,000 FCFA
  - Revenus ce mois : 1,200,000 FCFA
  - Commissions MLM : 1,867,500 FCFA

- **Organisateurs** :
  - Total : 156
  - Niveau 1 : 89
  - Niveau 2 : 45
  - Niveau 3 : 12

- **Graphiques** :
  - Évolution des inscriptions (6 derniers mois)
  - Revenus mensuels
  - Tournois par jeu

---

### 2. **Page Utilisateurs** (`/admin/users`)

**Filtres** :
- Statut (Actif, Banni, Inactif)
- Rôle (Standard, Organisateur, Modérateur, Arbitre, Admin)
- Badge Organisateur (Niveau 0/1/2/3)
- Date d'inscription

**Liste des Utilisateurs** (Tableau) :
- ID
- Nom
- Email
- Pays
- Rôle
- Badge (si organisateur)
- Statut
- Date d'inscription
- Actions : [Voir] [Modifier] [Bannir] [Révoquer rôle]

---

### 3. **Page Détails Utilisateur** (`/admin/users/:id`)

**Sections** :
- **Informations** :
  - Photo de profil
  - Nom, Email, Pays, Téléphone
  - Date d'inscription
  - Dernière connexion
  - Device fingerprint (si banni)

- **Rôles et Permissions** :
  - Rôle actuel : [Select dropdown]
  - Badge organisateur : [Select dropdown]
  - [Enregistrer les modifications]

- **Statistiques** :
  - MLM Rank
  - Tournois joués / organisés
  - Matchs joués
  - Revenus générés (si organisateur)

- **Historique des Sanctions** :
  - Liste des avertissements, bannissements

- **Actions Admin** :
  - [Bannir utilisateur] (temporaire ou définitif)
  - [Lever bannissement]
  - [Réinitialiser mot de passe]
  - [Supprimer compte]

---

### 4. **Page Tournois Admin** (`/admin/tournaments`)

**Filtres** :
- Statut
- Jeu
- Organisateur
- Type (Gratuit/Payant)
- Date

**Liste des Tournois** (Tableau) :
- ID
- Nom
- Organisateur
- Jeu
- Date
- Participants
- Prize Pool
- Statut
- Actions : [Voir] [Annuler] [Modifier]

---

### 5. **Page Finances** (`/admin/finances`)

**Onglets** :
- **Revenus Globaux** :
  - Graphique : Évolution des revenus (par mois)
  - Total commissions MLM : X FCFA
  - Total frais certification organisateurs : X FCFA
  - Total transactions : X FCFA

- **Paiements en Attente** :
  - Liste des prize pools à distribuer
  - Liste des paiements organisateurs en attente

- **Transactions** :
  - Historique complet des transactions
  - Filtres : Type (Inscription, Prize pool, Certification)
  - Export CSV

---

### 6. **Page Statistiques Globales** (`/admin/stats`)

**Sections** :
- **Utilisateurs** :
  - Taux de rétention (7j, 30j)
  - Taux de conversion visiteur → inscrit
  - Utilisateurs actifs par pays

- **Tournois** :
  - Nombre moyen de participants
  - Taux de remplissage
  - Tournois par jeu (graphique)

- **Engagement** :
  - Temps moyen par session
  - Pages les plus visitées
  - Taux de rebond

- **Finances** :
  - Revenus par canal (tournois, certifications)
  - Coût d'acquisition utilisateur
  - LTV (Lifetime Value)

---

## 📝 Formulaires Détaillés

### Formulaire Inscription Saison Division

**Champs** :
- Sélectionner l'équipe (select : liste des équipes de l'utilisateur pour le jeu de la saison)
- Accepter les règles de la saison (checkbox)
- Paiement : X MLC (affichage du montant)
- Bouton : [Payer et S'inscrire]

**Processus** :
1. Utilisateur clique sur [S'inscrire] depuis la page `/divisions`
2. Modal/Page s'ouvre avec formulaire
3. Validation : Vérifier que l'utilisateur a assez de MLC
4. Redirection vers paiement Mobile Money (si nécessaire)
5. Confirmation : "Inscription réussie ! Vous êtes inscrit avec l'équipe [Nom]"

---

### Formulaire Recharge MLM Coins

**Champs** :
- Montant à recharger (select ou input) :
  - 10 MLC = 500 FCFA
  - 20 MLC = 1,000 FCFA
  - 50 MLC = 2,500 FCFA
  - 100 MLC = 5,000 FCFA
  - Montant personnalisé (input)
- Méthode de paiement (select) :
  - Orange Money
  - MTN Mobile Money
  - Moov Money
- Numéro de téléphone Mobile Money (input)
- Bouton : [Recharger]

**Processus** :
1. Redirection vers API Mobile Money
2. Utilisateur confirme sur son téléphone
3. Retour sur MLM avec confirmation
4. Solde mis à jour

---

### Formulaire Contester Résultat

**Champs** :
- Raison de la contestation (textarea)
- Upload screenshot/vidéo de votre côté (file input)
- Bouton : [Soumettre la contestation]

**Affichage** :
- Résultat déclaré par l'adversaire
- Screenshot soumis par l'adversaire

---

### Formulaire Noter un Organisateur

**Champs** (après fin de tournoi) :
- Note (1-5 étoiles)
- Commentaire (textarea optionnel)
- Critères à noter :
  - Organisation (1-5)
  - Communication (1-5)
  - Respect des règles (1-5)
- Bouton : [Soumettre l'avis]

---

## 🧩 Composants Réutilisables

### 1. **TournamentCard**
- Affichage d'un tournoi sous forme de carte
- Props : tournamentId, name, game, organizer, date, participants, prizePool, status
- Actions : [Voir] [S'inscrire]

### 2. **MatchCard**
- Affichage d'un match
- Props : player1, player2, score, status, date
- Actions : [Soumettre résultat] [Voir détails]

### 3. **UserAvatar**
- Avatar utilisateur avec badge (si organisateur, modérateur, arbitre, admin)
- Props : userId, name, avatar, badge, size

### 4. **BracketVisualization**
- Composant pour afficher un bracket de tournoi (arbre d'élimination)
- Props : matches, format
- Interactif : Clic sur un match pour voir détails

### 5. **NotificationBell**
- Icône de cloche avec badge (nombre de notifications non lues)
- Dropdown au clic avec liste des notifications

### 6. **ChatWidget**
- Widget de chat réutilisable (tournoi, match, support)
- Props : chatId, chatType, participants
- Features : Messages temps réel, upload images, emojis

### 7. **PaymentModal**
- Modal pour paiement Mobile Money
- Props : amount, purpose, callback
- Affiche QR code ou instructions de paiement

### 8. **ConfirmationDialog**
- Modal de confirmation pour actions critiques
- Props : title, message, onConfirm, onCancel
- Ex : "Êtes-vous sûr de vouloir annuler ce tournoi ?"

### 9. **LoadingSpinner**
- Spinner de chargement
- Props : size, message

### 10. **EmptyState**
- Affichage quand une liste est vide
- Props : icon, message, action
- Ex : "Aucun tournoi disponible. [Créer un tournoi]"

---

## 🔔 Modals & Popups

### Modal Connexion Rapide
- Ouvert depuis n'importe quelle page
- Formulaire simplifié : Email, Password, [Se connecter]

### Modal Inscription Rapide
- Formulaire simplifié avec étapes minimales

### Modal Confirmation Inscription Tournoi
- "Confirmer votre inscription au tournoi [Nom]"
- Affiche : Frais, Date, Règles
- [Confirmer] [Annuler]

### Modal Détails Match
- Affichage complet du match
- Joueurs, Scores, Screenshots, Statut
- Actions selon contexte

### Modal Upload Screenshot
- Drag & drop ou click to upload
- Prévisualisation
- Validation (taille max, formats acceptés)

### Modal Paiement
- Intégration Mobile Money
- Instructions de paiement
- Statut en temps réel

### Modal Succès
- "✅ Action réussie !"
- Message personnalisé
- Auto-close après 3s ou [OK]

### Modal Erreur
- "❌ Une erreur est survenue"
- Message d'erreur
- [Réessayer] [Annuler]

---

## 🔔 Notifications & Alertes

### Types de Notifications

**In-App (Dropdown Cloche)** :
- Nouveau match programmé
- Résultat de match validé
- Adversaire a soumis un résultat (action requise)
- Tournoi va commencer dans 1h
- Vous avez gagné un match
- Prize pool reçu
- Message reçu d'un organisateur
- Demande de certification approuvée/rejetée

**Email** :
- Confirmation d'inscription
- Rappel de match (24h avant)
- Résultat de match en attente de validation
- Nouveau tournoi correspondant à vos intérêts
- Prize pool transféré
- Décision d'arbitrage

**Toasts (Angular Material Snackbar)** :
- Actions réussies : "✅ Inscription réussie"
- Erreurs : "❌ Échec de la connexion"
- Avertissements : "⚠️ Vous avez 1h pour soumettre le résultat"

---

## 📱 Responsive Design

### Breakpoints

```
Mobile : < 768px
Tablet : 768px - 1024px
Desktop : > 1024px
```

### Adaptations Mobile

**Navigation** :
- Menu hamburger (☰) remplace menu horizontal
- Avatar et notifications restent visibles

**Cards** :
- Passage en 1 colonne
- Réduction des marges

**Tableaux** :
- Passage en cartes empilables
- Ou scroll horizontal

**Formulaires** :
- Inputs full-width
- Boutons full-width

**Bracket** :
- Scroll horizontal
- Zoom/Pinch to zoom

---

## 🎨 Thème & Style

### Couleurs (Suggestion)

```
Primaire : #1E88E5 (Bleu) - Boutons, liens
Secondaire : #FFA726 (Orange) - Accents, badges
Succès : #66BB6A (Vert)
Avertissement : #FFA726 (Orange)
Erreur : #EF5350 (Rouge)
Neutre : #757575 (Gris)
Background : #FAFAFA (Gris clair)
Text : #212121 (Noir)
```

### Typographie

```
Titres : Montserrat (bold)
Corps : Roboto (regular)
```

### Icônes

- **Library** : Material Icons ou Font Awesome
- Exemples : 🏆 (trophée), ⚽ (football), 🎮 (jeu), 👤 (utilisateur)

---

## 🚀 Features Avancées

### PWA Features

- **Installation** : Prompt pour installer l'app sur mobile
- **Offline Mode** : Cache des pages principales
- **Notifications Push** : Pour matchs, tournois

### Temps Réel (WebSocket)

- Chat en temps réel
- Mise à jour des scores en direct
- Notifications instantanées
- Mise à jour du bracket en temps réel

### Internationalisation (i18n)

- Français (par défaut)
- Anglais
- Autres langues africaines (futur)

---

## 📋 Checklist Fonctionnalités Frontend

### Pages
- [ ] Toutes les pages publiques
- [ ] Toutes les pages utilisateur standard
- [ ] Toutes les pages organisateur
- [ ] Toutes les pages modérateur
- [ ] Toutes les pages arbitre
- [ ] Toutes les pages admin

### Formulaires
- [ ] Inscription/Connexion
- [ ] Créer tournoi
- [ ] Soumettre résultat
- [ ] Recharge MLC
- [ ] Demande certification

### Composants
- [ ] TournamentCard
- [ ] MatchCard
- [ ] BracketVisualization
- [ ] ChatWidget
- [ ] Notifications

### Responsive
- [ ] Mobile (< 768px)
- [ ] Tablet (768-1024px)
- [ ] Desktop (> 1024px)

### PWA
- [ ] Service Worker
- [ ] Manifest
- [ ] Offline cache
- [ ] Install prompt

---

**Fin du Document**

Ce document sera mis à jour au fur et à mesure du développement.
