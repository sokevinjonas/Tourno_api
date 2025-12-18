# 🎨 FRONTEND ANGULAR - MOBILE LEAGUE MANAGER (MLM) - MVP

## Spécifications de l'Interface Utilisateur

**Version** : 1.0 MVP
**Date** : Décembre 2024
**Framework** : Angular 17+ (Standalone Components)
**Type** : Application Web Progressive (PWA) - Desktop & Mobile Responsive

**⚠️ IMPORTANT** : Ce document décrit **UNIQUEMENT** les fonctionnalités du MVP. Les éléments suivants sont **EXCLUS** du MVP et seront développés en Phase 2+ :
- ❌ Système de Divisions (D1, D2, D3, D4)
- ❌ MLM Rank / Système ELO
- ❌ Chat intégré
- ❌ Rôle Arbitre (seuls les Modérateurs gèrent les litiges)
- ❌ Recharge/Retrait de fonds (Mobile Money)
- ❌ Système de badges organisateur multi-niveaux
- ❌ Autres formats de tournois (seul le Format Suisse est implémenté)
- ❌ Notations des organisateurs

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'Ensemble de l'API](#vue-densemble-de-lapi)
2. [Architecture de Navigation](#architecture-de-navigation)
3. [Pages Publiques](#pages-publiques)
4. [Pages Joueur](#pages-joueur)
5. [Pages Organisateur](#pages-organisateur)
6. [Pages Modérateur](#pages-modérateur)
7. [Pages Administrateur](#pages-administrateur)
8. [Composants Réutilisables](#composants-réutilisables)
9. [Responsive Design](#responsive-design)

---

## 🌐 Vue d'Ensemble de l'API

### Base URL
```
API: http://localhost:8000/api
Frontend: http://localhost:4200
```

### Authentification
- **Type** : Laravel Sanctum (Bearer Token)
- **Header** : `Authorization: Bearer {token}`
- **Token stockage** : localStorage ou sessionStorage

### Endpoints Principaux (48 routes disponibles)

**Authentification** (3 endpoints)
- `POST /auth/magic-link/send` - Envoyer magic link
- `POST /auth/magic-link/verify` - Vérifier magic link
- `GET /auth/oauth/{provider}/redirect` - OAuth (Google, Apple, Facebook)
- `GET /auth/oauth/{provider}/callback` - OAuth callback

**Profils** (5 endpoints)
- `GET /profile` - Mon profil
- `POST /profile` - Créer/Mettre à jour profil
- `GET /profiles/pending` - Profils en attente (Modérateur)
- `POST /profiles/{id}/validate` - Valider profil (Modérateur)
- `POST /profiles/{id}/reject` - Rejeter profil (Modérateur)

**Comptes de Jeu** (5 endpoints REST)
- `GET /game-accounts` - Mes comptes de jeu
- `POST /game-accounts` - Ajouter compte de jeu
- `GET /game-accounts/{id}` - Détails compte
- `PUT /game-accounts/{id}` - Modifier compte
- `DELETE /game-accounts/{id}` - Supprimer compte

**Wallet** (5 endpoints)
- `GET /wallet` - Mon wallet
- `GET /wallet/balance` - Mon solde
- `GET /wallet/transactions` - Historique transactions
- `GET /wallet/statistics` - Statistiques wallet
- `POST /wallet/add-funds` - Ajouter fonds (Admin uniquement)

**Tournois** (14 endpoints)
- `GET /tournaments` - Liste tournois (filtres: status, game_type)
- `POST /tournaments` - Créer tournoi (Organisateur/Admin)
- `GET /tournaments/upcoming` - Tournois à venir
- `GET /tournaments/registering` - Tournois en inscription
- `GET /tournaments/{id}` - Détails tournoi
- `PUT /tournaments/{id}` - Modifier tournoi
- `DELETE /tournaments/{id}` - Supprimer tournoi
- `POST /tournaments/{id}/status` - Changer statut
- `POST /tournaments/{id}/register` - S'inscrire
- `POST /tournaments/{id}/withdraw` - Se retirer
- `GET /tournaments/{id}/participants` - Liste participants
- `GET /tournaments/{id}/leaderboard` - Classement
- `GET /tournaments/{id}/rounds` - Liste des rondes
- `GET /my/registrations` - Mes inscriptions

**Rondes & Swiss Format** (5 endpoints)
- `POST /tournaments/{id}/start` - Démarrer tournoi (génère ronde 1)
- `POST /tournaments/{id}/next-round` - Générer ronde suivante
- `POST /tournaments/{tournamentId}/rounds/{roundId}/complete` - Terminer ronde
- `POST /tournaments/{id}/complete` - Terminer tournoi + distribuer prix

**Matchs** (6 endpoints)
- `GET /matches/{id}` - Détails match
- `POST /matches/{id}/submit-result` - Soumettre résultat
- `GET /matches/my/matches` - Mes matchs
- `GET /matches/my/pending` - Mes matchs en attente
- `GET /matches/disputed/all` - Matchs disputés (Modérateur)
- `POST /matches/{id}/validate` - Valider match disputé (Modérateur)

---

## 🗺️ Architecture de Navigation

### Menu Principal (Header)

#### **Utilisateur Non Connecté**
```
┌────────────────────────────────────────────────────────┐
│  [LOGO MLM]  Accueil  Tournois  Se connecter           │
└────────────────────────────────────────────────────────┘
```

**Menu Items**:
- **Accueil** → `/` (Landing page)
- **Tournois** → `/tournaments` (Liste publique)
- **Se connecter** → `/login` (Page de connexion)

---

#### **Joueur Connecté**
```
┌──────────────────────────────────────────────────────────────┐
│  [LOGO]  Tournois  Mes Matchs  [💰 X pièces]  [Avatar ▼]    │
└──────────────────────────────────────────────────────────────┘
```

**Menu Items**:
- **Tournois** → `/tournaments`
- **Mes Matchs** → `/my-matches`
- **Solde MLM** → Affichage en temps réel
- **Avatar Dropdown**:
  - Mon Profil → `/profile`
  - Mes Comptes de Jeu → `/game-accounts`
  - Mon Wallet → `/wallet`
  - Mes Inscriptions → `/my-registrations`
  - Se déconnecter

---

#### **Organisateur**
```
┌──────────────────────────────────────────────────────────────────┐
│  [LOGO]  Tournois  Mes Tournois  [Créer]  [💰 X pièces]  [🔵▼]  │
└──────────────────────────────────────────────────────────────────┘
```

**Items supplémentaires**:
- **Mes Tournois** → `/organizer/tournaments`
- **Créer Tournoi** → `/organizer/create` (bouton CTA)

---

#### **Modérateur**
```
┌──────────────────────────────────────────────────────────────────┐
│  [LOGO]  Profils  Matchs Disputés  [🛡️ Modérateur]  [Avatar ▼]  │
└──────────────────────────────────────────────────────────────────┘
```

**Menu Items**:
- **Profils** → `/moderator/profiles` (Validation de profils)
- **Matchs Disputés** → `/moderator/disputes` (Résolution de litiges)
- **Badge Modérateur** → 🛡️ visible

---

#### **Administrateur**
```
┌───────────────────────────────────────────────────────────────────┐
│  [LOGO]  Dashboard  Utilisateurs  Tournois  [👑 Admin]  [Avatar]  │
└───────────────────────────────────────────────────────────────────┘
```

**Menu Items**:
- **Dashboard** → `/admin/dashboard`
- **Utilisateurs** → `/admin/users`
- **Tournois** → `/admin/tournaments`
- **Wallet Management** → `/admin/wallets`

---

## 🌍 Pages Publiques

### 1. **Page d'Accueil** (`/`)

**Sections**:

**Hero Section**:
```
┌──────────────────────────────────────────────────┐
│  Mobile League Manager                           │
│  La Plateforme de Tournois E-sports Mobile      │
│  en Afrique                                      │
│                                                  │
│  [Se connecter]  [Découvrir]                    │
└──────────────────────────────────────────────────┘
```

**Comment ça marche** (3 étapes):
1. **Crée ton compte** → Authentification sans mot de passe (OAuth/Magic Link)
2. **Complète ton profil** → WhatsApp, Pays, Ville + Comptes de jeu avec screenshots
3. **Joue et gagne** → Inscris-toi aux tournois, joue, gagne des pièces MLM

**Jeux supportés**:
- E-football (eFootball PES)
- FC Mobile (EA Sports)
- Dream League Soccer

**Footer**:
- À propos | Contact | CGU | Confidentialité

---

### 2. **Page Connexion** (`/login`)

**Interface**:
```
┌─────────────────────────────────────────┐
│  Connexion à MLM                        │
│                                         │
│  [🔵 Continuer avec Google]            │
│  [⚫ Continuer avec Apple]             │
│  [🔵 Continuer avec Facebook]          │
│                                         │
│  ────────── ou ──────────              │
│                                         │
│  📧 Connexion par email                │
│  [  Email  ]                           │
│  [Recevoir un lien de connexion]       │
│                                         │
│  Le lien expire dans 15 minutes        │
└─────────────────────────────────────────┘
```

**Workflow Magic Link**:
1. Utilisateur saisit email
2. `POST /api/auth/magic-link/send { email }`
3. Affichage: "✉️ Email envoyé! Vérifiez votre boîte"
4. Utilisateur clique sur lien: `/auth/verify?token=XXXXX`
5. `POST /api/auth/magic-link/verify { token }`
6. Si succès → Redirection `/home` (ou `/profile/complete` si nouveau)
7. Si échec → "Lien invalide ou expiré"

**Workflow OAuth**:
1. Clic sur bouton OAuth
2. `GET /api/auth/oauth/{provider}/redirect`
3. Redirection vers provider (Google/Apple/Facebook)
4. Callback: `GET /api/auth/oauth/{provider}/callback`
5. Réception du token Sanctum
6. Redirection `/home` ou `/profile/complete`

**Note**: Pas de page d'inscription séparée. L'inscription se fait automatiquement à la première connexion.

---

### 3. **Page Tournois Publique** (`/tournaments`)

**Filtres**:
```
Jeu: [Tous ▼] [E-football] [FC Mobile] [DLS]
Statut: [Tous ▼] [Inscriptions ouvertes] [En cours] [Terminé]
```

**API Call**:
```typescript
GET /api/tournaments?game_type=efootball&status=registering
```

**Liste Tournois** (Cards):
```
┌──────────────────────────────────────┐
│  🏆 Championnat E-football Cameroun  │
│  🎮 E-football                       │
│  👤 Organisateur: JohnDoe            │
│  📅 25 Déc 2024, 14:00              │
│  💰 Frais: 5 pièces                 │
│  👥 16/32 participants               │
│  🏅 Prize Pool: 160 pièces          │
│  [Voir détails]                      │
└──────────────────────────────────────┘
```

---

## 🏠 Pages Joueur

### 1. **Compléter le Profil** (`/profile/complete`)

**Important**: Cette page s'affiche après la première connexion.

**Formulaire (Étape 1 - Informations Personnelles)**:
```
Nom complet: [_________________]
Numéro WhatsApp: [_________________]
Pays: [Cameroun ▼]
Ville: [Yaoundé ▼]
Date de naissance: [15/05/1995]
Bio (optionnel): [____________]

[Suivant →]
```

**API Call**:
```typescript
POST /api/profile
{
  "whatsapp_number": "+237XXXXXXXXX",
  "country": "Cameroun",
  "city": "Yaoundé",
  "date_of_birth": "1995-05-15",
  "bio": "Passionné de mobile gaming"
}
```

**Étape 2 - Comptes de Jeu**:
```
┌────────────────────────────────────┐
│  E-football                        │
│  Pseudo: [ProGamer123______]       │
│  Screenshot: [📷 Uploader]         │
│                                    │
│  [+ Ajouter FC Mobile]             │
│  [+ Ajouter Dream League Soccer]   │
└────────────────────────────────────┘

[Terminer et Soumettre]
```

**API Call**:
```typescript
POST /api/game-accounts
FormData {
  game_type: 'efootball',
  in_game_name: 'ProGamer123',
  screenshot: File
}
```

**Après soumission**:
- Affichage: "✅ Profil soumis! En attente de validation par un modérateur"
- Statut du profil: `pending`
- Utilisateur NE PEUT PAS s'inscrire aux tournois tant que profil non validé

---

### 2. **Page Mon Profil** (`/profile`)

**API Call**:
```typescript
GET /api/profile
```

**Affichage**:
```
┌─────────────────────────────────────────┐
│  [Avatar]  John Doe                     │
│  📧 john@example.com                    │
│  📱 +237XXXXXXXXX                       │
│  🌍 Cameroun, Yaoundé                   │
│                                         │
│  Statut du profil: ✅ Validé           │
│  Validé le: 20 Déc 2024                │
│                                         │
│  [Modifier]                             │
└─────────────────────────────────────────┘
```

**Si profil rejeté**:
```
┌─────────────────────────────────────────┐
│  Statut: ❌ Rejeté                      │
│  Raison: Screenshot non lisible         │
│  [Modifier et Resoumettre]              │
└─────────────────────────────────────────┘
```

---

### 3. **Page Mes Comptes de Jeu** (`/game-accounts`)

**API Call**:
```typescript
GET /api/game-accounts
```

**Liste**:
```
┌─────────────────────────────────────┐
│  🎮 E-football                      │
│  Pseudo: ProGamer123                │
│  [Voir screenshot] [Modifier] [X]   │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  ⚽ FC Mobile                        │
│  [+ Ajouter un compte]              │
└─────────────────────────────────────┘
```

**Formulaire Ajout/Modification** (Modal):
```
Type de jeu: [E-football ▼]
Pseudo: [_________________]
Screenshot: [📷 Uploader ou glisser-déposer]

[Enregistrer]
```

---

### 4. **Page Mon Wallet** (`/wallet`)

**API Calls**:
```typescript
GET /api/wallet
GET /api/wallet/transactions?limit=50&offset=0
GET /api/wallet/statistics
```

**Affichage**:
```
┌────────────────────────────────────┐
│  💰 Mon Solde                      │
│  10.00 pièces MLM                  │
│  (= 5,000 FCFA)                    │
└────────────────────────────────────┘

┌────────────────────────────────────┐
│  📊 Statistiques                   │
│  Total crédité: 10.00 pièces      │
│  Total débité: 0.00 pièces        │
│  Transactions: 1                   │
└────────────────────────────────────┘

┌────────────────────────────────────┐
│  📋 Historique des Transactions    │
│                                    │
│  ✅ Bonus de bienvenue            │
│  +10.00 pièces                     │
│  20 Déc 2024, 14:23               │
└────────────────────────────────────┘
```

**⚠️ Note MVP**: Pas de recharge ni de retrait dans le MVP. L'admin peut ajouter des fonds manuellement.

---

### 5. **Page Tournois** (`/tournaments`)

**API Calls**:
```typescript
GET /api/tournaments?status=registering
GET /api/tournaments/upcoming?game_type=efootball
GET /api/tournaments/registering
```

**Card Tournoi**:
```
┌───────────────────────────────────────┐
│  🏆 Championnat E-football            │
│  🎮 E-football | Format Suisse        │
│  👤 Par: JohnDoe                      │
│  📅 25-26 Déc 2024                   │
│  💰 Frais: 5 pièces                  │
│  👥 16/32 places                     │
│  🏅 Prize: 160 pièces                │
│  [S'inscrire]                         │
└───────────────────────────────────────┘
```

---

### 6. **Page Détails Tournoi** (`/tournaments/:id`)

**API Calls**:
```typescript
GET /api/tournaments/{id}
GET /api/tournaments/{id}/check-registration
GET /api/tournaments/{id}/leaderboard
GET /api/tournaments/{id}/participants
GET /api/tournaments/{id}/rounds
```

**En-tête**:
```
┌──────────────────────────────────────┐
│  🏆 Championnat E-football Cameroun  │
│  Status: 🟢 Inscriptions ouvertes    │
│  Par: JohnDoe (Organisateur)         │
│  📅 25 Déc 2024, 14:00              │
│  🎮 E-football | Format Suisse       │
└──────────────────────────────────────┘
```

**Onglets**:

**Tab 1: Informations**
```
Description:
Premier tournoi national E-football

Format: Suisse
Participants: 16/32
Frais d'inscription: 5 pièces
Prize Pool: 160 pièces
Distribution:
  1er: 80 pièces
  2e: 50 pièces
  3e: 30 pièces

Règles:
- Format Swiss: 5 rondes
- Chaque victoire = 3 points
- Nul = 1 point
- Défaite = 0 point
- Screenshot obligatoire

[S'inscrire maintenant] (si places disponibles)
```

**API Call Inscription**:
```typescript
POST /api/tournaments/{id}/register
{
  "game_account_id": 1
}
```

**Vérifications côté frontend avant inscription**:
1. ✅ Profil validé
2. ✅ Compte de jeu correspondant au type de tournoi
3. ✅ Solde suffisant (>= entry_fee)
4. ✅ Places disponibles
5. ✅ Période d'inscription active

**Tab 2: Participants**
```
┌────────────────────────────────────┐
│  👤 ProGamer123 (E-football)       │
│  👤 ElitePlayer (E-football)       │
│  👤 ChampionX (E-football)         │
│  ...                               │
│  Total: 16/32                      │
└────────────────────────────────────┘
```

**Tab 3: Classement** (si tournoi en cours/terminé)
```
┌──────────────────────────────────────┐
│  Rang | Joueur      | Pts | V-N-D   │
│  1    | ProGamer123 | 12  | 4-0-1   │
│  2    | ElitePlayer | 10  | 3-1-1   │
│  3    | ChampionX   |  9  | 3-0-2   │
│  ...                                 │
└──────────────────────────────────────┘
```

**Tab 4: Rondes & Matchs** (si tournoi démarré)
```
┌────────────────────────────────────┐
│  📍 Ronde 1 - Terminée             │
│  ProGamer123 3-1 ElitePlayer ✅    │
│  ChampionX 2-2 MasterPro ✅        │
│                                    │
│  📍 Ronde 2 - En cours             │
│  ProGamer123 vs ChampionX ⏳       │
│  ElitePlayer vs MasterPro ⏳       │
└────────────────────────────────────┘
```

---

### 7. **Page Mes Matchs** (`/my-matches`)

**API Calls**:
```typescript
GET /api/matches/my/pending
GET /api/matches/my/matches
```

**Onglets**: À Jouer | Historique

**Tab: À Jouer**
```
┌────────────────────────────────────────┐
│  🏆 Championnat E-football             │
│  Ronde 2                               │
│  Vous vs ElitePlayer                   │
│  📅 Aujourd'hui, 15:00                 │
│  Status: ⏳ À jouer                    │
│  [Soumettre résultat]                  │
└────────────────────────────────────────┘
```

**Tab: Historique**
```
┌────────────────────────────────────────┐
│  🏆 Championnat E-football             │
│  Ronde 1                               │
│  Vous (3) - (1) ChampionX              │
│  ✅ Victoire | 20 Déc 2024            │
│  [Voir détails]                        │
└────────────────────────────────────────┘
```

---

### 8. **Page Soumettre Résultat** (`/matches/:id/submit`)

**API Call**:
```typescript
GET /api/matches/{id}
```

**Interface**:
```
┌────────────────────────────────────────┐
│  📝 Soumettre Résultat                 │
│                                        │
│  🏆 Championnat E-football - Ronde 2  │
│  Vous vs ElitePlayer                   │
│  📅 25 Déc 2024                        │
│                                        │
│  Votre score: [3_]                     │
│  Score adversaire: [1_]                │
│                                        │
│  Screenshot du résultat: *             │
│  [📷 Uploader screenshot]              │
│  (JPEG, PNG, max 5MB)                  │
│                                        │
│  Prévisualisation:                     │
│  [Image preview]                       │
│                                        │
│  Commentaire (optionnel):              │
│  [________________________]            │
│                                        │
│  [Soumettre le résultat]               │
└────────────────────────────────────────┘
```

**API Call**:
```typescript
POST /api/matches/{id}/submit-result
FormData {
  own_score: 3,
  opponent_score: 1,
  screenshot: File,
  comment: "GG wp"
}
```

**Workflow Validation Automatique**:

**Scénario 1: Vous êtes le premier à soumettre**
```
✅ Résultat soumis!
En attente de la soumission de votre adversaire.
```

**Scénario 2: Scores concordent (3-1 vs 1-3)**
```
✅ Match validé automatiquement!
Les scores correspondent. Votre victoire a été enregistrée.
+3 points ajoutés au classement.
```

**Scénario 3: Scores divergent (vous: 3-1, adversaire: 2-2)**
```
⚠️ Litige détecté!
Vos scores ne correspondent pas.
Le match a été soumis à un modérateur pour validation.

Votre soumission: 3-1
Soumission adversaire: 2-2

Un modérateur examinera les screenshots et déterminera le score correct.
```

---

### 9. **Page Mes Inscriptions** (`/my-registrations`)

**API Call**:
```typescript
GET /api/my/registrations
```

**Liste**:
```
┌────────────────────────────────────────┐
│  🏆 Championnat E-football             │
│  Status: 🟢 En cours                   │
│  📅 25-26 Déc 2024                    │
│  Votre classement: 2/16 (10 pts)      │
│  [Voir détails]                        │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│  🏆 Coupe FC Mobile                    │
│  Status: 🔵 Inscriptions ouvertes      │
│  📅 30 Déc 2024                       │
│  [Se retirer]                          │
└────────────────────────────────────────┘
```

**API Call Retrait**:
```typescript
POST /api/tournaments/{id}/withdraw
```

---

## 🏆 Pages Organisateur

### 1. **Page Mes Tournois** (`/organizer/tournaments`)

**API Call**:
```typescript
GET /api/tournaments/my/tournaments
```

**Filtres**:
```
Statut: [Tous ▼] [Brouillon] [Inscriptions] [En cours] [Terminé]
Jeu: [Tous ▼]
```

**Tableau**:
```
┌──────────────────────────────────────────────────────────────┐
│  Nom              | Jeu     | Date      | Parts | Statut    │
│  Championnat E-f  | E-foot  | 25/12     | 16/32 | En cours │ [Gérer]
│  Coupe FC Mobile  | FC Mob  | 30/12     |  5/16 | Inscrip. │ [Modifier]
└──────────────────────────────────────────────────────────────┘
```

---

### 2. **Page Créer Tournoi** (`/organizer/create`)

**Formulaire**:
```
┌────────────────────────────────────────┐
│  Créer un Tournoi                      │
│                                        │
│  Nom: [_________________________]      │
│  Jeu: [E-football ▼]                   │
│  Description:                          │
│  [___________________________]         │
│                                        │
│  Participants max: [32__]              │
│  Frais d'inscription: [5__] pièces    │
│                                        │
│  Distribution des prix:                │
│  1er: [80__] pièces                    │
│  2e:  [50__] pièces                    │
│  3e:  [30__] pièces                    │
│  (Total = entry_fee × participants)    │
│                                        │
│  📅 Période d'inscription              │
│  Début: [20/12/2024 10:00]            │
│  Fin:   [24/12/2024 23:59]            │
│                                        │
│  📅 Dates du tournoi                   │
│  Début: [25/12/2024 14:00]            │
│  Fin:   [26/12/2024 20:00] (opt.)     │
│                                        │
│  Règles (optionnel):                   │
│  [___________________________]         │
│                                        │
│  [Créer le tournoi]                    │
└────────────────────────────────────────┘
```

**API Call**:
```typescript
POST /api/tournaments
{
  "name": "Championnat E-football Cameroun",
  "description": "Premier tournoi national",
  "game_type": "efootball",
  "format": "swiss",
  "max_participants": 32,
  "entry_fee": 5.00,
  "prize_pool": 0,
  "prize_distribution": {
    "1": 80.00,
    "2": 50.00,
    "3": 30.00
  },
  "status": "upcoming",
  "registration_start": "2024-12-20T10:00:00Z",
  "registration_end": "2024-12-24T23:59:59Z",
  "start_date": "2024-12-25T14:00:00Z",
  "rules": "Format Swiss, screenshots obligatoires"
}
```

**Validations Frontend**:
- registration_end > registration_start
- start_date > registration_end
- end_date > start_date (si fourni)
- max_participants >= 2
- entry_fee >= 0
- Somme prize_distribution <= entry_fee × max_participants

---

### 3. **Page Gérer Tournoi** (`/organizer/tournaments/:id`)

**API Calls**:
```typescript
GET /api/tournaments/{id}
GET /api/tournaments/{id}/participants
GET /api/tournaments/{id}/rounds
```

**Onglets**: Vue d'ensemble | Participants | Rondes | Prize Pool

**Tab: Vue d'ensemble**
```
┌────────────────────────────────────────┐
│  🏆 Championnat E-football             │
│  Status: 🟢 En cours                   │
│                                        │
│  📊 Statistiques                       │
│  Inscrits: 16/32                       │
│  Prize Pool: 80 pièces                 │
│  Rondes jouées: 2/5                    │
│                                        │
│  Actions:                              │
│  [Modifier] [Changer statut]           │
└────────────────────────────────────────┘
```

**Actions disponibles selon statut**:

**Si `status = registering`**:
```
[Démarrer le tournoi] → POST /api/tournaments/{id}/start
```

**Si `status = in_progress` et ronde terminée**:
```
[Générer ronde suivante] → POST /api/tournaments/{id}/next-round
```

**Si toutes rondes terminées**:
```
[Terminer le tournoi] → POST /api/tournaments/{id}/complete
```

**Tab: Participants**
```
┌────────────────────────────────────┐
│  👤 ProGamer123 (E-football)       │
│  📧 player1@example.com            │
│  📱 +237XXXXXXXXX                  │
│                                    │
│  👤 ElitePlayer (E-football)       │
│  ...                               │
└────────────────────────────────────┘
```

**Tab: Rondes**
```
┌────────────────────────────────────┐
│  📍 Ronde 1 - ✅ Terminée          │
│  ├─ ProGamer123 3-1 ElitePlayer    │
│  ├─ ChampionX 2-2 MasterPro        │
│  └─ ...                            │
│                                    │
│  📍 Ronde 2 - 🟢 En cours          │
│  ├─ ProGamer123 vs ChampionX ⏳    │
│  └─ ElitePlayer vs MasterPro ⏳    │
│                                    │
│  [Terminer cette ronde]            │
│  [Générer ronde suivante]          │
└────────────────────────────────────┘
```

**Workflow Gestion des Rondes**:

1. **Démarrer le tournoi**
   ```typescript
   POST /api/tournaments/{id}/start
   // Génère automatiquement la Ronde 1
   // Status: registering → in_progress
   // Calcule nombre de rondes: N = ⌈log₂(16)⌉ = 4 rondes
   ```

2. **Générer ronde suivante** (après que tous matchs soient terminés)
   ```typescript
   POST /api/tournaments/{id}/next-round
   // Appariement Swiss: joueurs même score qui ne se sont pas affrontés
   ```

3. **Terminer le tournoi**
   ```typescript
   POST /api/tournaments/{id}/complete
   // Status: in_progress → completed
   // Distribution automatique des prix selon prize_distribution
   ```

---

## 🛡️ Pages Modérateur

### 1. **Dashboard Modérateur** (`/moderator/dashboard`)

**Sections**:
```
┌────────────────────────────────────┐
│  📋 File d'attente                 │
│  Profils en attente: 5             │
│  Matchs disputés: 3                │
└────────────────────────────────────┘

┌────────────────────────────────────┐
│  📊 Mes Stats                      │
│  Profils validés ce mois: 45       │
│  Litiges résolus: 12               │
└────────────────────────────────────┘
```

---

### 2. **Page Validation Profils** (`/moderator/profiles`)

**API Call**:
```typescript
GET /api/profiles/pending
```

**Liste**:
```
┌────────────────────────────────────────┐
│  👤 John Doe                           │
│  📧 john@example.com                   │
│  🌍 Cameroun, Yaoundé                  │
│  📱 +237XXXXXXXXX                      │
│  📅 Soumis le: 20 Déc 2024            │
│                                        │
│  Comptes de jeu:                       │
│  🎮 E-football: ProGamer123            │
│  [Voir screenshot]                     │
│                                        │
│  [✅ Valider] [❌ Rejeter]             │
└────────────────────────────────────────┘
```

**Action Valider**:
```typescript
POST /api/profiles/{id}/validate
// → validation_status = 'validated'
// → Crédit automatique de 10 pièces
```

**Action Rejeter** (Modal):
```
┌────────────────────────────────────┐
│  Raison du rejet:                  │
│  [Screenshot non lisible_______]   │
│  [Confirmer le rejet]              │
└────────────────────────────────────┘
```

```typescript
POST /api/profiles/{id}/reject
{
  "reason": "Screenshot non lisible"
}
// → validation_status = 'rejected'
```

---

### 3. **Page Matchs Disputés** (`/moderator/disputes`)

**API Call**:
```typescript
GET /api/matches/disputed/all
```

**Liste**:
```
┌────────────────────────────────────────────────┐
│  🏆 Championnat E-football - Ronde 2           │
│  ProGamer123 vs ElitePlayer                    │
│  ⚠️ Scores divergents                         │
│  📅 25 Déc 2024                               │
│  [Examiner]                                    │
└────────────────────────────────────────────────┘
```

---

### 4. **Page Détails Litige** (`/moderator/disputes/:id`)

**API Call**:
```typescript
GET /api/matches/{id}
```

**Interface**:
```
┌──────────────────────────────────────────────────┐
│  🏆 Championnat E-football - Ronde 2             │
│  ProGamer123 vs ElitePlayer                      │
│                                                  │
│  📊 Résultats Soumis:                            │
│                                                  │
│  👤 ProGamer123 (a soumis):                      │
│  Score déclaré: 3-1 (Victoire)                   │
│  Screenshot: [Voir image]                        │
│  Commentaire: "Match serré, victoire méritée"    │
│                                                  │
│  👤 ElitePlayer (a soumis):                      │
│  Score déclaré: 2-2 (Nul)                        │
│  Screenshot: [Voir image]                        │
│  Commentaire: "C'était un nul clair"             │
│                                                  │
│  📸 Screenshots:                                 │
│  [Image ProGamer123] [Image ElitePlayer]         │
│                                                  │
│  ✍️ Décision du Modérateur:                      │
│  Score Player1: [3_]                             │
│  Score Player2: [1_]                             │
│                                                  │
│  [Valider ce résultat]                           │
└──────────────────────────────────────────────────┘
```

**API Call Validation**:
```typescript
POST /api/matches/{id}/validate
{
  "player1_score": 3,
  "player2_score": 1
}
// → Match status: disputed → completed
// → Met à jour stats des joueurs (wins, points)
// → Met à jour classement du tournoi
```

---

## 👑 Pages Administrateur

### 1. **Dashboard Admin** (`/admin/dashboard`)

**Widgets**:
```
┌──────────────────────────────┐
│  👥 Utilisateurs             │
│  Total: 15,234               │
│  Nouveaux (7j): 245          │
│  Actifs (7j): 8,432          │
└──────────────────────────────┘

┌──────────────────────────────┐
│  🏆 Tournois                 │
│  Total: 456                  │
│  Actifs: 12                  │
│  Ce mois: 34                 │
└──────────────────────────────┘

┌──────────────────────────────┐
│  💰 Économie                 │
│  Pièces en circulation: 45K  │
│  Transactions (7j): 1,234    │
└──────────────────────────────┘
```

---

### 2. **Page Utilisateurs** (`/admin/users`)

**API Call**:
```typescript
GET /api/admin/users (à implémenter)
```

**Tableau**:
```
┌────────────────────────────────────────────────────────────┐
│  Nom       | Email          | Rôle     | Status  | Actions │
│  John Doe  | john@mail.com  | Player   | Actif   | [Voir]  │
│  Jane Org  | jane@mail.com  | Organiz. | Actif   | [Voir]  │
│  Bob Mod   | bob@mail.com   | Moderat. | Actif   | [Voir]  │
└────────────────────────────────────────────────────────────┘
```

---

### 3. **Page Gestion Wallets** (`/admin/wallets`)

**Fonction**: Ajouter des fonds manuellement aux utilisateurs

**Interface**:
```
┌────────────────────────────────────┐
│  Ajouter des Fonds                 │
│                                    │
│  Utilisateur: [Rechercher___]      │
│  Montant: [___] pièces             │
│  Description: [_____________]      │
│                                    │
│  [Ajouter les fonds]               │
└────────────────────────────────────┘
```

**API Call**:
```typescript
POST /api/wallet/add-funds
{
  "user_id": 5,
  "amount": 50.00,
  "description": "Récompense pour contribution"
}
```

---

## 🧩 Composants Réutilisables

### 1. **TournamentCard**
```typescript
@Component({
  selector: 'app-tournament-card',
  inputs: ['tournament']
})
```

**Props**:
- tournament: { id, name, game_type, organizer, start_date, max_participants, entry_fee, prize_pool, status }

**Template**:
```html
<div class="tournament-card">
  <h3>{{ tournament.name }}</h3>
  <div class="game-type">{{ tournament.game_type }}</div>
  <div class="organizer">Par: {{ tournament.organizer.name }}</div>
  <div class="date">{{ tournament.start_date | date }}</div>
  <div class="stats">
    <span>{{ registrations }}/{{ tournament.max_participants }}</span>
    <span>{{ tournament.entry_fee }} pièces</span>
  </div>
  <button (click)="viewDetails()">Voir détails</button>
</div>
```

---

### 2. **MatchCard**
```typescript
@Component({
  selector: 'app-match-card',
  inputs: ['match']
})
```

**Props**:
- match: { id, tournament, player1, player2, player1_score, player2_score, status, scheduled_at }

**Template**:
```html
<div class="match-card">
  <div class="tournament">{{ match.tournament.name }}</div>
  <div class="players">
    <span>{{ match.player1.name }}</span>
    <span class="vs">vs</span>
    <span>{{ match.player2.name }}</span>
  </div>
  <div class="score" *ngIf="match.status === 'completed'">
    {{ match.player1_score }} - {{ match.player2_score }}
  </div>
  <div class="status">{{ getStatusLabel(match.status) }}</div>
  <button *ngIf="canSubmitResult()" (click)="submitResult()">
    Soumettre résultat
  </button>
</div>
```

---

### 3. **UserAvatar**
```typescript
@Component({
  selector: 'app-user-avatar',
  inputs: ['user', 'size']
})
```

**Template**:
```html
<div class="avatar" [class.size-sm]="size === 'sm'">
  <img [src]="user.avatar_url || defaultAvatar" [alt]="user.name">
  <span class="badge" *ngIf="user.role !== 'player'">
    {{ getRoleBadge(user.role) }}
  </span>
</div>
```

**Badges**:
- Admin: 👑
- Modérateur: 🛡️
- Organisateur: 🔵

---

### 4. **LeaderboardTable**
```typescript
@Component({
  selector: 'app-leaderboard',
  inputs: ['leaderboard']
})
```

**Template**:
```html
<table class="leaderboard">
  <thead>
    <tr>
      <th>Rang</th>
      <th>Joueur</th>
      <th>Points</th>
      <th>V-N-D</th>
    </tr>
  </thead>
  <tbody>
    <tr *ngFor="let entry of leaderboard">
      <td>{{ entry.rank }}</td>
      <td>
        <app-user-avatar [user]="entry.user" size="sm"></app-user-avatar>
        {{ entry.user.name }}
      </td>
      <td>{{ entry.points }}</td>
      <td>{{ entry.wins }}-{{ entry.draws }}-{{ entry.losses }}</td>
    </tr>
  </tbody>
</table>
```

---

### 5. **LoadingSpinner**
```html
<div class="spinner-container">
  <div class="spinner"></div>
  <p *ngIf="message">{{ message }}</p>
</div>
```

---

### 6. **EmptyState**
```html
<div class="empty-state">
  <div class="icon">{{ icon }}</div>
  <h3>{{ title }}</h3>
  <p>{{ message }}</p>
  <button *ngIf="action" (click)="action.handler()">
    {{ action.label }}
  </button>
</div>
```

**Exemples d'usage**:
```typescript
// Aucun tournoi disponible
<app-empty-state
  icon="🏆"
  title="Aucun tournoi disponible"
  message="Revenez plus tard ou créez votre propre tournoi"
  [action]="{ label: 'Créer un tournoi', handler: goToCreate }">
</app-empty-state>

// Aucun match
<app-empty-state
  icon="⚽"
  title="Aucun match en attente"
  message="Inscrivez-vous à un tournoi pour commencer à jouer">
</app-empty-state>
```

---

## 📱 Responsive Design

### Breakpoints
```scss
$mobile: 768px;
$tablet: 1024px;

@media (max-width: $mobile) {
  // Mobile styles
}

@media (min-width: $mobile) and (max-width: $tablet) {
  // Tablet styles
}

@media (min-width: $tablet) {
  // Desktop styles
}
```

### Navigation Mobile
```html
<!-- Mobile: Hamburger menu -->
<header class="mobile">
  <button class="hamburger" (click)="toggleMenu()">☰</button>
  <div class="logo">MLM</div>
  <div class="wallet">{{ balance }} 💰</div>
</header>

<nav class="mobile-menu" [class.open]="menuOpen">
  <a routerLink="/tournaments">Tournois</a>
  <a routerLink="/my-matches">Mes Matchs</a>
  <a routerLink="/profile">Profil</a>
  <a (click)="logout()">Déconnexion</a>
</nav>
```

### Adaptations
- **Cards**: 1 colonne sur mobile, 2-3 colonnes sur tablet/desktop
- **Tableaux**: Scroll horizontal ou cards empilées sur mobile
- **Formulaires**: Inputs full-width sur mobile
- **Modals**: Full-screen sur mobile

---

## 🎨 Thème & Style

### Palette de Couleurs
```scss
$primary: #1E88E5;      // Bleu (boutons, liens)
$secondary: #FFA726;    // Orange (accents)
$success: #66BB6A;      // Vert (validations)
$warning: #FFA726;      // Orange (avertissements)
$error: #EF5350;        // Rouge (erreurs)
$neutral: #757575;      // Gris
$background: #FAFAFA;   // Gris clair
$text: #212121;         // Noir
```

### Typographie
```scss
font-family: 'Roboto', sans-serif;
```

### Status Colors
```scss
.status-upcoming { color: #2196F3; }      // Bleu
.status-registering { color: #4CAF50; }   // Vert
.status-in-progress { color: #FF9800; }   // Orange
.status-completed { color: #9E9E9E; }     // Gris
.status-disputed { color: #F44336; }      // Rouge
```

---

## 🔔 Notifications

### Types de Notifications In-App

**Format**:
```typescript
interface Notification {
  id: string;
  type: 'info' | 'success' | 'warning' | 'error';
  title: string;
  message: string;
  timestamp: Date;
  read: boolean;
  action?: { label: string; route: string };
}
```

**Exemples**:
- ✅ "Profil validé! 10 pièces ont été ajoutées à votre wallet"
- ⚽ "Nouveau match programmé: vs ElitePlayer - Ronde 2"
- ⏰ "Rappel: Match dans 1h contre ChampionX"
- 🏆 "Résultat validé: Victoire 3-1"
- ⚠️ "Match disputé: En attente de validation par modérateur"
- 💰 "Prix reçu: +80 pièces pour votre victoire!"

### Toast Messages (Snackbar)
```typescript
// Success
this.snackBar.open('✅ Inscription réussie!', 'OK', { duration: 3000 });

// Error
this.snackBar.open('❌ Solde insuffisant', 'OK', { duration: 5000 });

// Warning
this.snackBar.open('⚠️ Profil en attente de validation', 'OK', { duration: 4000 });
```

---

## 📋 Checklist Développement

### Pages Essentielles MVP
- [ ] Page Connexion (`/login`)
- [ ] Page Compléter Profil (`/profile/complete`)
- [ ] Page Tournois (`/tournaments`)
- [ ] Page Détails Tournoi (`/tournaments/:id`)
- [ ] Page Mes Matchs (`/my-matches`)
- [ ] Page Soumettre Résultat (`/matches/:id/submit`)
- [ ] Page Wallet (`/wallet`)
- [ ] Page Créer Tournoi (`/organizer/create`)
- [ ] Page Gérer Tournoi (`/organizer/tournaments/:id`)
- [ ] Page Validation Profils (`/moderator/profiles`)
- [ ] Page Matchs Disputés (`/moderator/disputes`)
- [ ] Page Admin Dashboard (`/admin/dashboard`)

### Services Angular
- [ ] AuthService (OAuth + Magic Link)
- [ ] ProfileService
- [ ] GameAccountService
- [ ] TournamentService
- [ ] MatchService
- [ ] WalletService
- [ ] NotificationService

### Guards
- [ ] AuthGuard (vérifie authentification)
- [ ] RoleGuard (vérifie rôle: organizer, moderator, admin)
- [ ] ProfileValidatedGuard (vérifie profil validé avant inscription tournoi)

### Interceptors
- [ ] AuthInterceptor (ajoute Bearer token)
- [ ] ErrorInterceptor (gère erreurs API)

---

**Fin du Document MVP**

Ce document sera mis à jour si de nouvelles fonctionnalités MVP sont ajoutées.
