# 📋 Cahier des Charges - Mobile League Manager (MLM)

**Version** : 1.0
**Date** : Décembre 2024
**Statut** : En cours de définition

---

## Table des Matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Architecture Système](#2-architecture-système)
3. [Modèle de Données](#3-modèle-de-données)
4. [Logique Métier Détaillée](#4-logique-métier-détaillée)
5. [Endpoints API](#5-endpoints-api)
6. [Système de Notifications](#6-système-de-notifications)
7. [Sécurité et Permissions](#7-sécurité-et-permissions)
8. [Questions et Décisions en Suspens](#8-questions-et-décisions-en-suspens)

---

## 1. Vue d'ensemble

### 1.1 Concept

Mobile League Manager (MLM) est une plateforme permettant l'organisation et la gestion de tournois pour les jeux de simulation de football mobile (Dream League Soccer, E-football, FC Mobile).

### 1.2 Principes Fondamentaux

- **Mobile-First** : Optimisé pour l'usage mobile
- **Temps Réel** : Synchronisation instantanée des données
- **Automatisation** : Minimiser l'intervention manuelle de l'organisateur
- **Fair-Play** : Système robuste de validation et de litiges
- **Scalabilité** : Support de petits tournois entre amis jusqu'aux grandes compétitions
- **Économie Intégrée** : Système de wallet, tournois payants, gains automatiques
- **Compétition Progressive** : Divisions hiérarchiques avec promotion/relégation

### 1.3 Acteurs du Système

| Acteur | Rôle | Permissions |
|--------|------|-------------|
| **Joueur** | Participant aux tournois | Inscription, déclaration de scores, chat |
| **Organisateur** | Créateur et gestionnaire d'un tournoi | Création, démarrage, arbitrage, gestion complète |
| **Arbitre** | Résolveur de litiges (optionnel) | Validation de scores en litige |
| **Administrateur** | Gestion globale de la plateforme | Modération, statistiques, gestion utilisateurs |

---

## 2. Architecture Système

### 2.1 Architecture en 3 Couches

```
┌─────────────────────────────────────────┐
│   COUCHE PRÉSENTATION (Ionic App)      │
│   - Interface utilisateur               │
│   - Gestion des événements              │
│   - Affichage temps réel                │
└─────────────────┬───────────────────────┘
                  │ HTTP/REST + WebSockets
┌─────────────────▼───────────────────────┐
│   LOGIQUE MÉTIER (Spring Boot API)     │
│   - Générateur de Bracket               │
│   - Moteur de Validation                │
│   - Calculateur ELO                     │
│   - Gestionnaire de Notifications       │
└─────────────────┬───────────────────────┘
                  │ JPA / Hibernate ORM
┌─────────────────▼───────────────────────┐
│   COUCHE DONNÉES (PostgreSQL)           │
│   - Persistance                         │
│   - Intégrité référentielle             │
│   - Historique                          │
└─────────────────────────────────────────┘
```

### 2.2 Stack Technique

**Backend**
- Framework : Spring Boot 3.x
- Langage : Java 17+ (LTS)
- API : RESTful (Spring Web)
- Authentification : Spring Security + JWT (JSON Web Tokens)
- Temps réel : Spring WebSocket (STOMP protocol)
- Queue : RabbitMQ (messages asynchrones)
- Cache : Redis (Spring Data Redis)
- ORM : JPA / Hibernate
- Validation : Bean Validation (JSR-380)
- Documentation API : SpringDoc OpenAPI (Swagger)

**Base de Données**
- Primaire : PostgreSQL 14+
- Schema migrations : Flyway / Liquibase
- Seeders : Java Faker pour données de test
- Connection Pool : HikariCP

**Frontend** (hors scope de cette API)
- Framework : Ionic + Angular/React
- Temps réel : Socket.io client

---

## 3. Modèle de Données

### 3.1 Entités Principales

#### 3.1.1 Users (Utilisateurs)

```
users
├── id (PK)
├── username (unique)
├── email (unique)
├── password_hash
├── phone_number (pour mobile money, nullable)
├── avatar_url
├── mlm_rank (points ELO global)
├── wallet_balance (solde en MLM Coins, décimal 10,2)
├── total_earned (total des gains, décimal 10,2)
├── total_spent (total des dépenses, décimal 10,2)
├── total_tournaments_played
├── total_wins
├── total_losses
├── win_rate (calculé)
├── current_division_id (FK -> divisions.id, nullable)
├── is_verified (boolean, pour retraits)
├── is_banned (boolean, défaut: false)
├── ban_reason (text, nullable)
├── banned_at (timestamp, nullable)
├── device_fingerprint (string, nullable: identifiant unique de l'appareil)
├── created_at
└── updated_at
```

**Règles de gestion** :
- `username` : 3-20 caractères, alphanumérique + underscore
- `mlm_rank` : Initialisation à 1000 points pour tout nouveau joueur
- `wallet_balance` : Initialisation à 0 coins, pas de solde négatif
- `win_rate` : Calculé automatiquement (total_wins / total_tournaments_played)
- `phone_number` : Requis pour effectuer des retraits (format international)
- `is_verified` : True après vérification d'identité (pour limiter fraudes)
- `is_banned` : True si le compte est banni définitivement
- `device_fingerprint` : Hash unique généré à partir des caractéristiques de l'appareil (pour empêcher les réinscriptions)

---

#### 3.1.2 Games (Jeux)

```
games
├── id (PK)
├── name (ex: "E-football 2024", "FC Mobile")
├── slug (ex: "efootball-2024")
├── icon_url
├── is_active
├── created_at
└── updated_at
```

**Règles de gestion** :
- Liste prédéfinie par les administrateurs
- Permet de filtrer les tournois par jeu

---

#### 3.1.3 Team_Accounts (Comptes d'Équipes/Pseudos de Jeu)

```
team_accounts
├── id (PK)
├── user_id (FK -> users.id)
├── game_id (FK -> games.id)
├── team_name (string: pseudo/nom d'équipe sur le jeu)
├── is_primary (boolean: équipe principale ou secondaire)
├── is_banned (boolean, défaut: false)
├── ban_reason (text, nullable)
├── banned_at (timestamp, nullable)
├── total_matches_played (integer)
├── total_matches_missed (integer)
├── created_at
└── updated_at

UNIQUE(user_id, game_id, team_name)
```

**Règles de gestion** :
- Chaque utilisateur peut avoir **maximum 2 équipes par jeu**
- `team_name` : Le pseudo/nom d'équipe utilisé dans le jeu (E-football, FC Mobile, Dream League Soccer)
- `is_primary` : True pour la première équipe créée, false pour la seconde
- `is_banned` : True si l'équipe a raté 18/38 journées (la moitié de la saison)
- Si les **2 équipes** d'un utilisateur sont bannies → Le compte utilisateur (`users.is_banned`) est banni définitivement
- Un utilisateur banni ne peut pas se réinscrire (blocage par `device_fingerprint`)

**Exemple** :
```
User: Karim
  ├─ E-football
  │   ├─ Équipe 1: "KarimFCPro" (is_primary: true)
  │   └─ Équipe 2: "KarimTheKing" (is_primary: false)
  ├─ FC Mobile
  │   ├─ Équipe 1: "Karim_24" (is_primary: true)
  │   └─ Équipe 2: "KarimMobile" (is_primary: false)
```

---

#### 3.1.4 Tournaments (Tournois)

```
tournaments
├── id (PK)
├── organizer_id (FK -> users.id)
├── game_id (FK -> games.id)
├── division_id (FK -> divisions.id, nullable pour tournois custom)
├── name
├── description
├── type (enum: 'knockout', 'league')
├── format (enum: '8', '16', '32' pour knockout)
├── status (enum: 'registration', 'ready', 'ongoing', 'completed', 'cancelled')
├── max_players
├── current_players_count
├── registration_deadline
├── match_deadline_hours (délai pour déclarer un score)
├── rules (JSON: règles spécifiques)
├── is_paid (boolean: gratuit ou payant)
├── entry_fee (décimal 10,2: frais d'inscription en MLM Coins)
├── prize_pool (décimal 10,2: cagnotte totale)
├── prize_distribution (JSON: répartition des gains)
├── platform_fee_percentage (décimal 5,2: commission plateforme, ex: 10%)
├── organizer_fee_percentage (décimal 5,2: commission organisateur, ex: 5%)
├── prize_description (texte libre pour description des prix)
├── is_public (boolean)
├── is_division_tournament (boolean: tournoi de division auto)
├── invitation_code (nullable, pour tournois privés)
├── started_at
├── completed_at
├── created_at
└── updated_at
```

**États du tournoi** :

| État | Description | Transitions possibles |
|------|-------------|----------------------|
| `registration` | Inscriptions ouvertes | → `ready` (si max_players atteint) |
| `ready` | Complet, en attente du démarrage | → `ongoing` (organisateur démarre) |
| `ongoing` | Tournoi en cours | → `completed` (finale validée) |
| `completed` | Terminé | ∅ |
| `cancelled` | Annulé | ∅ |

**Règles de gestion** :
- `format` : Uniquement puissances de 2 (8, 16, 32) pour type 'knockout'
- `match_deadline_hours` : Par défaut 24h (configurable par organisateur)
- `rules` : JSON permettant de stocker des règles custom (ex: {"max_team_rating": 85, "banned_teams": ["PSG", "Real Madrid"]})
- `is_paid` : Si true, entry_fee doit être > 0
- `prize_distribution` : JSON définissant la répartition, ex: {"1": 50, "2": 30, "3-4": 10} (en %)
- `prize_pool` : Calculé automatiquement = entry_fee × max_players × (1 - platform_fee% - organizer_fee%)
- `platform_fee_percentage` : Défaut 10% (commission pour la plateforme MLM)
- `organizer_fee_percentage` : Défaut 5% pour tournois custom, 0% pour tournois de division

---

#### 3.1.4 Tournament_Participants (Participants)

```
tournament_participants
├── id (PK)
├── tournament_id (FK -> tournaments.id)
├── user_id (FK -> users.id)
├── seed (position de tête de série, nullable)
├── status (enum: 'registered', 'eliminated', 'winner')
├── final_position (1 = champion, 2 = finaliste, etc.)
├── elo_before (MLM rank avant le tournoi)
├── elo_after (MLM rank après le tournoi)
├── elo_change (différence)
├── joined_at
├── eliminated_at
└── updated_at

UNIQUE(tournament_id, user_id)
```

**Règles de gestion** :
- Un joueur ne peut s'inscrire qu'une fois par tournoi
- `seed` : Déterminé lors du démarrage du tournoi (pour bracket generation)
- `elo_before` : Snapshot du MLM rank au moment de l'inscription

---

#### 3.1.5 Rounds (Tours)

```
rounds
├── id (PK)
├── tournament_id (FK -> tournaments.id)
├── round_number (1 = Quarts, 2 = Demi, 3 = Finale pour 8 joueurs)
├── name (ex: "Quarts de Finale", "Finale")
├── status (enum: 'pending', 'ongoing', 'completed')
├── deadline
├── created_at
└── updated_at

UNIQUE(tournament_id, round_number)
```

**Nomenclature des rounds** (pour knockout 8 joueurs) :
- Round 1 : "Quarts de Finale" (4 matchs)
- Round 2 : "Demi-Finales" (2 matchs)
- Round 3 : "Finale" (1 match)

**Pour 16 joueurs** :
- Round 1 : "Huitièmes de Finale" (8 matchs)
- Round 2 : "Quarts de Finale" (4 matchs)
- Round 3 : "Demi-Finales" (2 matchs)
- Round 4 : "Finale" (1 match)

---

#### 3.1.6 Matches (Matchs)

```
matches
├── id (PK)
├── tournament_id (FK -> tournaments.id)
├── round_id (FK -> rounds.id)
├── match_number (position dans le bracket)
├── player1_id (FK -> users.id, nullable)
├── player2_id (FK -> users.id, nullable)
├── winner_id (FK -> users.id, nullable)
├── status (enum: 'pending', 'ready', 'awaiting_results', 'disputed', 'completed', 'cancelled')
├── player1_score (nullable)
├── player2_score (nullable)
├── scheduled_at (nullable)
├── started_at (nullable)
├── completed_at (nullable)
├── next_match_id (FK -> matches.id, nullable)
├── next_match_position (enum: 'player1', 'player2', nullable)
├── created_at
└── updated_at
```

**États du match** :

| État | Description | Conditions |
|------|-------------|------------|
| `pending` | En attente de joueurs | Un ou deux joueurs manquants |
| `ready` | Prêt à être joué | Les 2 joueurs sont définis |
| `awaiting_results` | En attente de déclaration | Au moins 1 joueur a déclaré |
| `disputed` | Litige | Déclarations contradictoires |
| `completed` | Terminé et validé | Scores validés, vainqueur déterminé |
| `cancelled` | Annulé | Tournoi annulé ou forfait double |

**Règles de gestion** :
- `player1_id` et `player2_id` : Peuvent être NULL initialement (pour les matchs de tours avancés)
- `next_match_id` : Référence le match suivant (pour promotion automatique du vainqueur)
- `next_match_position` : Indique si le vainqueur va en position player1 ou player2 du prochain match

---

#### 3.1.7 Score_Declarations (Déclarations de Score)

```
score_declarations
├── id (PK)
├── match_id (FK -> matches.id)
├── user_id (FK -> users.id)
├── player1_score (score déclaré par le joueur)
├── player2_score
├── proof_url (URL de la capture d'écran)
├── declared_at
└── updated_at

UNIQUE(match_id, user_id)
```

**Règles de gestion** :
- Un joueur ne peut déclarer qu'une seule fois par match (ou modifier sa déclaration avant validation)
- `proof_url` : **Obligatoire** (stockage sur S3/Cloudinary)
- Validation automatique si les 2 déclarations correspondent

---

#### 3.1.8 Disputes (Litiges)

```
disputes
├── id (PK)
├── match_id (FK -> matches.id)
├── tournament_id (FK -> tournaments.id)
├── status (enum: 'pending', 'resolved', 'cancelled')
├── resolved_by (FK -> users.id, nullable)
├── final_player1_score (nullable)
├── final_player2_score (nullable)
├── resolution_notes (texte)
├── created_at
├── resolved_at
└── updated_at
```

**Règles de gestion** :
- Créé automatiquement quand les 2 déclarations ne correspondent pas
- L'organisateur (ou arbitre désigné) peut consulter les preuves et trancher
- Notification automatique à l'organisateur

---

#### 3.1.9 Tournament_Messages (Chat)

```
tournament_messages
├── id (PK)
├── tournament_id (FK -> tournaments.id)
├── user_id (FK -> users.id)
├── message (text)
├── is_system_message (boolean)
├── created_at
└── updated_at
```

**Règles de gestion** :
- Chat global par tournoi (pas de messages privés)
- Messages système (ex: "Le tournoi a démarré", "Match 1 validé")

---

#### 3.1.10 Notifications

```
notifications
├── id (PK)
├── user_id (FK -> users.id)
├── type (enum: 'match_ready', 'opponent_declared', 'deadline_reminder', 'dispute_created', etc.)
├── title
├── body
├── data (JSON: données contextuelles)
├── read_at (nullable)
├── created_at
└── updated_at
```

---

#### 3.1.11 Wallet_Transactions (Transactions du Wallet)

```
wallet_transactions
├── id (PK)
├── user_id (FK -> users.id)
├── type (enum: 'deposit', 'withdrawal', 'tournament_entry', 'tournament_win', 'refund', 'fee')
├── amount (décimal 10,2: montant en MLM Coins)
├── balance_before (décimal 10,2)
├── balance_after (décimal 10,2)
├── status (enum: 'pending', 'completed', 'failed', 'cancelled')
├── tournament_id (FK -> tournaments.id, nullable)
├── payment_method (enum: 'mobile_money', 'card', 'system', nullable)
├── payment_reference (string, nullable: référence du paiement externe)
├── description (text)
├── metadata (JSON: données supplémentaires)
├── created_at
└── updated_at
```

**Règles de gestion** :
- Toutes les opérations financières passent par cette table
- `balance_before` et `balance_after` : Snapshot pour audit
- `status` : Permet de gérer les transactions en attente de confirmation
- `type` détails :
  - `deposit` : Recharge de solde
  - `withdrawal` : Retrait de fonds
  - `tournament_entry` : Paiement frais d'inscription
  - `tournament_win` : Gain d'un tournoi
  - `refund` : Remboursement (tournoi annulé)
  - `fee` : Commission plateforme/organisateur

---

#### 3.1.12 Divisions (Divisions/Ligues Compétitives)

```
divisions
├── id (PK)
├── game_id (FK -> games.id)
├── name (ex: "Division 1 (D1)", "Division 2 (D2)", "Division 3 (D3)", "Division 4 (D4)")
├── slug (ex: "d1", "d2", "d3", "d4")
├── level (integer: 1 = D1 (Standard), 2 = D2, 3 = D3, 4 = D4 (Elite))
├── description
├── icon_url
├── entry_fee (décimal 10,2: frais pour rejoindre la saison)
├── min_mlm_rank (integer: MLM Rank minimum requis, nullable)
├── max_mlm_rank (integer: MLM Rank maximum autorisé, nullable)
├── max_members (integer: nombre max de joueurs)
├── current_members_count (integer)
├── match_days_per_week (integer: 3 par défaut)
├── match_day_schedule (JSON: ex: ["wednesday", "friday", "saturday"])
├── total_match_days_per_season (integer: 38 par défaut)
├── season_duration_months (integer: 3 par défaut)
├── season_months (JSON: ex: ["july", "august", "september"])
├── absence_ban_threshold (integer: 18 par défaut, moitié de total_match_days)
├── tournament_format (enum: 'league' pour divisions)
├── group_size (integer: 6 joueurs par groupe)
├── prizes (JSON: définition des prix)
├── promotion_count (integer: nombre de joueurs promus par saison)
├── relegation_count (integer: 3 derniers par groupe)
├── is_active (boolean)
├── created_at
└── updated_at
```

**Règles de gestion** :
- Hiérarchie des divisions par `level` (1 = D1 Standard, 2 = D2, 3 = D3, 4 = D4 Elite)
- `entry_fee` : Frais pour rejoindre la division pour toute la saison
- Format de jeu : **Groupes de 6 équipes** (poules)
- Chaque équipe joue contre les 5 autres de son groupe

**Calendrier de Saison** :
- **Durée** : 3 mois (Juillet, Août, Septembre)
- **Fréquence** : 3 journées par semaine
- **Exemple de planning** : Mercredi, Vendredi, Samedi
- **Total journées** : ~38 journées par saison
- **Journées manquées autorisées** : Maximum 17/38 (au-delà = bannissement de l'équipe)

**Système de Bannissement** :
- Si une équipe rate **18 journées ou plus** (la moitié) → L'équipe est **bannie définitivement**
- Si un utilisateur a **2 équipes bannies** → Le compte utilisateur est **banni définitivement**
- Utilisateur banni ne peut pas se réinscrire (blocage par `device_fingerprint`)

**Hiérarchie des Divisions** :
```
Division 4 (D4) - Elite      : Entry 100 coins (50,000 FCFA)
Division 3 (D3) - Excellence : Entry 60 coins (30,000 FCFA)
Division 2 (D2) - Confirmé   : Entry 40 coins (20,000 FCFA)
Division 1 (D1) - Standard   : Entry 40 coins (2,000 FCFA) + Qualifications
```

**Promotion/Relégation** :
- **D4, D3, D2** : Les 3 derniers de chaque groupe descendent à la division inférieure
- **D1** : Les 3 premiers de chaque groupe montent en D2
- **Phase de qualification D1** : 16 nouveaux qualifiés par saison (10 coins = 5,000 FCFA)

---

#### 3.1.13 Division_Memberships (Adhésion aux Divisions)

```
division_memberships
├── id (PK)
├── user_id (FK -> users.id)
├── team_account_id (FK -> team_accounts.id)
├── division_id (FK -> divisions.id)
├── status (enum: 'active', 'inactive', 'suspended', 'banned')
├── season_points (integer: points accumulés cette saison)
├── season_wins (integer)
├── season_losses (integer)
├── season_draws (integer)
├── match_days_played (integer: journées jouées)
├── match_days_missed (integer: journées ratées)
├── rank_in_division (integer: classement dans la division)
├── group_number (integer: numéro du groupe dans la division)
├── joined_at
├── left_at (nullable)
├── last_match_day_at (nullable)
└── updated_at

UNIQUE(team_account_id, division_id) WHERE status = 'active'
```

**Règles de gestion** :
- Une équipe ne peut être active que dans une seule division à la fois
- `team_account_id` : L'équipe (pseudo de jeu) utilisée pour cette division
- `season_points` : Réinitialisé à chaque nouvelle saison
- `match_days_played` + `match_days_missed` = total des journées écoulées
- `rank_in_division` : Mis à jour après chaque journée
- `group_number` : Groupe de 6 équipes dans la division

**Système de Suivi des Absences** :
- À chaque journée programmée, si l'équipe ne joue pas → `match_days_missed` s'incrémente
- Si `match_days_missed` ≥ 18 (la moitié de 38 journées) → L'équipe (`team_account.is_banned`) est bannie
- Si un utilisateur a ses 2 équipes bannies → Le compte (`users.is_banned`) est banni définitivement

**Promotion/Relégation** :
- **Promotion** : Top 3 de chaque groupe montent à la division supérieure
- **Relégation** : Les 3 derniers de chaque groupe descendent à la division inférieure
- **D1 spécial** : Les 3 premiers montent en D2, libérant des places pour les nouveaux qualifiés

---

#### 3.1.14 Withdrawal_Requests (Demandes de Retrait)

```
withdrawal_requests
├── id (PK)
├── user_id (FK -> users.id)
├── amount (décimal 10,2: montant en MLM Coins)
├── amount_fcfa (décimal 10,2: équivalent en FCFA = amount × 10)
├── phone_number (string: numéro mobile money)
├── payment_method (enum: 'orange_money', 'mtn_money', 'moov_money', 'bank_transfer')
├── status (enum: 'pending', 'processing', 'completed', 'rejected', 'cancelled')
├── transaction_id (FK -> wallet_transactions.id, nullable)
├── admin_notes (text, nullable)
├── rejection_reason (text, nullable)
├── processed_at (nullable)
├── processed_by (FK -> users.id, nullable: admin qui traite)
├── created_at
└── updated_at
```

**Règles de gestion** :
- Montant minimum : 10 coins (100 FCFA)
- Montant maximum par jour : 1000 coins (10,000 FCFA)
- `status` workflow :
  - `pending` : En attente de traitement par admin
  - `processing` : En cours de traitement (paiement mobile money en cours)
  - `completed` : Retrait effectué avec succès
  - `rejected` : Refusé (solde insuffisant, infraction, etc.)
  - `cancelled` : Annulé par l'utilisateur
- Le solde est débité immédiatement (transaction `pending`), remboursé si `rejected`

---

### 3.2 Relations entre Entités

```
Users (1) ──────< Tournaments (organizer_id)
Users (1) ──────< Tournament_Participants
Users (1) ──────< Matches (player1_id, player2_id, winner_id)
Users (1) ──────< Score_Declarations
Users (1) ──────< Tournament_Messages
Users (1) ──────< Notifications
Users (1) ──────< Wallet_Transactions
Users (1) ──────< Withdrawal_Requests
Users (1) ──────< Division_Memberships
Users (N) ─────── Divisions (current_division_id)

Games (1) ──────< Tournaments
Games (1) ──────< Divisions

Divisions (1) ──────< Tournaments
Divisions (1) ──────< Division_Memberships

Tournaments (1) ──────< Tournament_Participants
Tournaments (1) ──────< Rounds
Tournaments (1) ──────< Matches
Tournaments (1) ──────< Disputes
Tournaments (1) ──────< Tournament_Messages
Tournaments (1) ──────< Wallet_Transactions

Rounds (1) ──────< Matches

Matches (1) ──────< Score_Declarations
Matches (1) ──────< Disputes
Matches (1) ──────< Matches (next_match_id, auto-référence)

Withdrawal_Requests (1) ─────── Wallet_Transactions
```

---

## 4. Logique Métier Détaillée

### 4.1 Cycle de Vie d'un Tournoi Knockout

#### 4.1.1 Phase 1 : Création et Inscription

**Workflow** :

1. **Création** (Organisateur)
   - L'organisateur remplit le formulaire de création
   - Validation des données
   - Génération d'un `invitation_code` si tournoi privé
   - État initial : `registration`

2. **Inscription des joueurs**
   - Les joueurs s'inscrivent via le lien public ou le code d'invitation
   - Création d'une entrée dans `tournament_participants`
   - Incrémentation de `current_players_count`
   - Snapshot du `mlm_rank` actuel → `elo_before`

3. **Passage à l'état `ready`**
   - **Automatique** : Dès que `current_players_count == max_players`
   - **Ou Manuel** : L'organisateur peut clôturer les inscriptions avant (si flexible)
   - Notification envoyée à tous les participants : "Le tournoi est complet !"

---

#### 4.1.2 Phase 2 : Génération du Bracket

**Déclencheur** : L'organisateur clique sur "Démarrer le tournoi"

**Algorithme de Seeding** :

**Option A : Seeding par MLM Rank (Recommandé)**
```
1. Récupérer tous les participants
2. Trier par `elo_before` décroissant
3. Assigner seed = position dans le tri (1 = meilleur joueur)
```

**Option B : Seeding Aléatoire**
```
1. Récupérer tous les participants
2. Mélanger aléatoirement (shuffle)
3. Assigner seed = position après mélange
```

**Génération des Matchs** :

Pour un tournoi à **8 joueurs** (1 tour = Quarts, 2 = Demi, 3 = Finale) :

```
Round 1 (Quarts de Finale) - 4 matchs :
  Match 1 : Seed 1 vs Seed 8  →  Vainqueur → Match 5 (player1)
  Match 2 : Seed 4 vs Seed 5  →  Vainqueur → Match 5 (player2)
  Match 3 : Seed 2 vs Seed 7  →  Vainqueur → Match 6 (player1)
  Match 4 : Seed 3 vs Seed 6  →  Vainqueur → Match 6 (player2)

Round 2 (Demi-Finales) - 2 matchs :
  Match 5 : Vainqueur M1 vs Vainqueur M2  →  Vainqueur → Match 7 (player1)
  Match 6 : Vainqueur M3 vs Vainqueur M4  →  Vainqueur → Match 7 (player2)

Round 3 (Finale) - 1 match :
  Match 7 : Vainqueur M5 vs Vainqueur M6  →  Champion
```

**Pseudo-code** :
```php
function generateBracket(Tournament $tournament) {
    // 1. Seeding
    $participants = $tournament->participants()
        ->orderBy('elo_before', 'desc')
        ->get();

    foreach ($participants as $index => $participant) {
        $participant->seed = $index + 1;
        $participant->save();
    }

    // 2. Créer les rounds
    $totalPlayers = $tournament->max_players;
    $totalRounds = log($totalPlayers, 2); // 8 joueurs = 3 rounds

    for ($i = 1; $i <= $totalRounds; $i++) {
        Round::create([
            'tournament_id' => $tournament->id,
            'round_number' => $i,
            'name' => getRoundName($i, $totalPlayers),
            'status' => $i == 1 ? 'ongoing' : 'pending'
        ]);
    }

    // 3. Créer les matchs du Round 1
    $round1 = $tournament->rounds()->where('round_number', 1)->first();
    $matchPairings = getStandardPairings($totalPlayers); // [1-8, 4-5, 2-7, 3-6]

    foreach ($matchPairings as $index => $pairing) {
        $player1 = $participants->where('seed', $pairing[0])->first();
        $player2 = $participants->where('seed', $pairing[1])->first();

        Match::create([
            'tournament_id' => $tournament->id,
            'round_id' => $round1->id,
            'match_number' => $index + 1,
            'player1_id' => $player1->user_id,
            'player2_id' => $player2->user_id,
            'status' => 'ready'
        ]);
    }

    // 4. Créer les matchs vides pour les rounds suivants
    createNextRoundMatches($tournament, $totalRounds);

    // 5. Lier les matchs (next_match_id, next_match_position)
    linkMatches($tournament);

    // 6. Mettre à jour le statut du tournoi
    $tournament->status = 'ongoing';
    $tournament->started_at = now();
    $tournament->save();
}
```

---

#### 4.1.3 Phase 3 : Déroulement et Validation des Matchs

**Workflow de déclaration de score** :

```
1. Joueur A joue son match contre Joueur B
2. Joueur A va sur l'app et déclare le score : "3-1" + upload capture d'écran
   → Création d'une entrée dans score_declarations
   → Match passe en status 'awaiting_results'
   → Notification à Joueur B : "Ton adversaire a déclaré un score"

3. Joueur B déclare aussi : "3-1" + capture d'écran
   → Création d'une deuxième entrée dans score_declarations

4. Algorithme de Validation :
   SI (score_A == score_B) :
       → Validation automatique
       → Match.status = 'completed'
       → Match.player1_score = score_A.player1_score
       → Match.player2_score = score_A.player2_score
       → Match.winner_id = déterminerVainqueur(scores)
       → Promotion automatique du vainqueur au match suivant
       → Notification aux 2 joueurs : "Match validé"

   SINON :
       → Litige détecté
       → Match.status = 'disputed'
       → Créer une entrée dans disputes
       → Notification à l'Organisateur : "Arbitrage requis"
       → Bloquer l'avancement du tournoi jusqu'à résolution
```

**Code de validation** :
```php
function validateMatch(Match $match) {
    $declarations = $match->scoreDeclarations;

    if ($declarations->count() < 2) {
        return; // Attente de la 2ème déclaration
    }

    $decl1 = $declarations[0];
    $decl2 = $declarations[1];

    // Vérifier si les scores correspondent
    if ($decl1->player1_score == $decl2->player1_score &&
        $decl1->player2_score == $decl2->player2_score) {

        // ✅ VALIDATION AUTOMATIQUE
        $match->player1_score = $decl1->player1_score;
        $match->player2_score = $decl1->player2_score;
        $match->winner_id = determineWinner($match);
        $match->status = 'completed';
        $match->completed_at = now();
        $match->save();

        // Promouvoir le vainqueur
        promoteWinner($match);

        // Notifications
        notifyPlayers($match, 'match_validated');

    } else {
        // ❌ LITIGE
        $match->status = 'disputed';
        $match->save();

        Dispute::create([
            'match_id' => $match->id,
            'tournament_id' => $match->tournament_id,
            'status' => 'pending'
        ]);

        notifyOrganizer($match->tournament, 'arbitrage_required');
    }
}
```

---

#### 4.1.4 Phase 4 : Promotion Automatique

**Principe** : Dès qu'un match est validé, le vainqueur doit être automatiquement placé dans le match suivant.

**Exemple** :
```
Match 1 (Round 1) : Joueur A vs Joueur B
  → Vainqueur : Joueur A
  → next_match_id = Match 5
  → next_match_position = 'player1'

Action :
  Match 5.player1_id = Joueur A
  SI (Match 5.player1_id ET Match 5.player2_id sont définis) :
      Match 5.status = 'ready'
      Notification aux 2 joueurs : "Votre match est prêt !"
```

**Code** :
```php
function promoteWinner(Match $match) {
    if (!$match->next_match_id) {
        // C'est la finale, pas de promotion
        finalizeTournament($match->tournament);
        return;
    }

    $nextMatch = Match::find($match->next_match_id);

    if ($match->next_match_position == 'player1') {
        $nextMatch->player1_id = $match->winner_id;
    } else {
        $nextMatch->player2_id = $match->winner_id;
    }

    // Si les 2 joueurs sont maintenant définis, le match est prêt
    if ($nextMatch->player1_id && $nextMatch->player2_id) {
        $nextMatch->status = 'ready';
        notifyPlayers($nextMatch, 'match_ready');
    }

    $nextMatch->save();
}
```

---

#### 4.1.5 Phase 5 : Gestion des Litiges

**Workflow d'arbitrage** :

1. **Détection du litige** (automatique)
   - Déclarations contradictoires
   - Création d'une entrée dans `disputes`
   - Notification à l'organisateur

2. **Interface d'arbitrage** (Organisateur)
   - Vue côte-à-côte des 2 captures d'écran
   - Boutons : "Valider score de Joueur A" / "Valider score de Joueur B" / "Annuler le match"

3. **Résolution**
   ```php
   function resolveDispute(Dispute $dispute, $validScore) {
       $match = $dispute->match;

       $match->player1_score = $validScore['player1_score'];
       $match->player2_score = $validScore['player2_score'];
       $match->winner_id = determineWinner($match);
       $match->status = 'completed';
       $match->completed_at = now();
       $match->save();

       $dispute->status = 'resolved';
       $dispute->resolved_by = auth()->id();
       $dispute->resolved_at = now();
       $dispute->resolution_notes = "Arbitré par l'organisateur";
       $dispute->save();

       promoteWinner($match);
       notifyPlayers($match, 'dispute_resolved');
   }
   ```

---

#### 4.1.6 Phase 6 : Gestion des Forfaits

**Scénario 1 : Un seul joueur déclare**

- Joueur A déclare le score
- Joueur B ne déclare rien
- **Délai** : `match_deadline_hours` (ex: 24h après la déclaration de A)

**Workflow** :
```
1. A déclare → Timer démarre
2. Notifications de rappel à B : à 12h, 6h, 1h avant deadline
3. Si deadline dépassée :
   → Notification à l'Organisateur : "Joueur B n'a pas déclaré, valider le score de A ?"
   → Options :
      - Valider automatiquement le score de A
      - Déclarer B forfait (victoire automatique de A)
      - Prolonger le délai
```

**Scénario 2 : Aucun joueur ne déclare**

- Deadline du round dépassée
- Aucune déclaration

**Workflow** :
```
→ Notification à l'Organisateur
→ Options :
   - Prolonger le délai
   - Annuler le match (double forfait)
   - Désigner un vainqueur manuellement
```

---

#### 4.1.7 Phase 7 : Finalisation du Tournoi

**Déclencheur** : La finale est validée

**Actions** :
```
1. Déterminer le classement final :
   - 1er : Vainqueur de la finale
   - 2ème : Perdant de la finale
   - 3-4ème : Perdants des demi-finales
   - 5-8ème : Perdants des quarts

2. Mettre à jour tournament_participants.final_position

3. Calculer les changements ELO pour tous les participants

4. Mettre à jour les profils utilisateurs :
   - total_tournaments_played += 1
   - total_wins += 1 (pour le vainqueur)
   - mlm_rank += elo_change

5. Mettre à jour le tournoi :
   - status = 'completed'
   - completed_at = now()

6. Notifications :
   - Au vainqueur : "Félicitations, vous avez gagné !"
   - À tous : "Le tournoi est terminé, consultez le classement final"
```

---

### 4.2 Système de Classement ELO (MLM Rank)

#### 4.2.1 Principe

Le MLM Rank est un système inspiré du classement ELO des échecs, adapté aux tournois.

**Caractéristiques** :
- Tous les joueurs commencent à **1000 points**
- Les gains/pertes dépendent de :
  - La différence de niveau entre les 2 joueurs
  - L'importance du tournoi (nombre de participants)
  - Le tour atteint

#### 4.2.2 Formule de Calcul

**Formule ELO standard** :
```
Nouveau Rating = Ancien Rating + K × (Résultat - Résultat Attendu)

Où :
- K = Facteur K (32 pour joueurs normaux, 40 pour débutants)
- Résultat = 1 (victoire), 0.5 (nul), 0 (défaite)
- Résultat Attendu = 1 / (1 + 10^((Rating_Adversaire - Rating_Joueur) / 400))
```

**Adaptation pour MLM** :

1. **Pondération par l'importance du tournoi**
   ```
   K_tournoi = K_base × Multiplicateur_Tournoi

   Multiplicateur_Tournoi :
   - 8 joueurs  : 1.0
   - 16 joueurs : 1.5
   - 32 joueurs : 2.0
   ```

2. **Pondération par le tour**
   ```
   Points_Bonus_Tour :
   - Quarts de finale : +0
   - Demi-finale      : +10
   - Finale           : +20
   - Victoire finale  : +50
   ```

**Exemple de calcul** :
```php
function calculateEloChange(User $player, User $opponent, $result, Tournament $tournament, $round) {
    $K_base = 32;
    $K_tournament = $K_base * getTournamentMultiplier($tournament->max_players);

    $expectedScore = 1 / (1 + pow(10, ($opponent->mlm_rank - $player->mlm_rank) / 400));
    $eloChange = $K_tournament * ($result - $expectedScore);

    // Bonus de tour
    $roundBonus = getRoundBonus($round, $result);

    return round($eloChange + $roundBonus);
}

// Exemple :
// Joueur A (1200) bat Joueur B (1000) en finale d'un tournoi de 16 joueurs
// K = 32 × 1.5 = 48
// Expected = 1 / (1 + 10^((1000-1200)/400)) = 0.76
// Change = 48 × (1 - 0.76) = 11.52
// Bonus finale = +20 (participation) + 50 (victoire) = +70
// Total = 11.52 + 70 = +81.52 ≈ +82 points
```

#### 4.2.3 Application en Fin de Tournoi

```php
function updateEloRatings(Tournament $tournament) {
    $participants = $tournament->participants;

    foreach ($participants as $participant) {
        $eloChange = 0;

        // Récupérer tous les matchs du joueur
        $matches = Match::where('tournament_id', $tournament->id)
            ->where(function($q) use ($participant) {
                $q->where('player1_id', $participant->user_id)
                  ->orWhere('player2_id', $participant->user_id);
            })
            ->where('status', 'completed')
            ->get();

        foreach ($matches as $match) {
            $opponent = $match->getOpponent($participant->user_id);
            $result = $match->winner_id == $participant->user_id ? 1 : 0;

            $eloChange += calculateEloChange(
                $participant->user,
                $opponent,
                $result,
                $tournament,
                $match->round->round_number
            );
        }

        // Mettre à jour le participant
        $participant->elo_after = $participant->elo_before + $eloChange;
        $participant->elo_change = $eloChange;
        $participant->save();

        // Mettre à jour le profil global
        $user = $participant->user;
        $user->mlm_rank += $eloChange;
        $user->save();
    }
}
```

---

### 4.3 Tournois de Type Ligue (Round Robin)

#### 4.3.1 Principe

Tous les joueurs s'affrontent en matches **aller simple** (ou aller-retour selon configuration).

**Classement par points** :
- Victoire : 3 points
- Nul : 1 point (si applicable)
- Défaite : 0 point

**Départage** (en cas d'égalité de points) :
1. Différence de buts
2. Confrontation directe
3. Nombre de victoires
4. Tirage au sort

#### 4.3.2 Génération du Calendrier

**Pour N joueurs, nombre de matchs** :
- Aller simple : N × (N - 1) / 2
- Aller-retour : N × (N - 1)

**Exemple : 6 joueurs, aller simple** :
```
Journée 1 : A-B, C-D, E-F
Journée 2 : A-C, B-E, D-F
Journée 3 : A-D, B-F, C-E
Journée 4 : A-E, B-D, C-F
Journée 5 : A-F, B-C, D-E
```

**Algorithme Round-Robin** :
```php
function generateLeagueMatches(Tournament $tournament) {
    $participants = $tournament->participants->shuffle();
    $n = $participants->count();

    if ($n % 2 != 0) {
        // Ajouter un "bye" fictif
        $participants->push(null);
        $n++;
    }

    $totalRounds = $n - 1;
    $matchesPerRound = $n / 2;

    for ($round = 1; $round <= $totalRounds; $round++) {
        $roundEntity = Round::create([
            'tournament_id' => $tournament->id,
            'round_number' => $round,
            'name' => "Journée $round"
        ]);

        for ($match = 0; $match < $matchesPerRound; $match++) {
            $home = $participants[($round + $match - 1) % ($n - 1)];
            $away = $participants[($n - 1 - $match + $round) % ($n - 1)];

            if ($match == 0) {
                $away = $participants[$n - 1];
            }

            if ($home && $away) {
                Match::create([
                    'tournament_id' => $tournament->id,
                    'round_id' => $roundEntity->id,
                    'player1_id' => $home->user_id,
                    'player2_id' => $away->user_id,
                    'status' => 'ready'
                ]);
            }
        }
    }
}
```

#### 4.3.3 Calcul du Classement

```php
function getLeagueStandings(Tournament $tournament) {
    $participants = $tournament->participants;
    $standings = [];

    foreach ($participants as $participant) {
        $matches = Match::where('tournament_id', $tournament->id)
            ->where(function($q) use ($participant) {
                $q->where('player1_id', $participant->user_id)
                  ->orWhere('player2_id', $participant->user_id);
            })
            ->where('status', 'completed')
            ->get();

        $points = 0;
        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($matches as $match) {
            $isPlayer1 = $match->player1_id == $participant->user_id;
            $playerScore = $isPlayer1 ? $match->player1_score : $match->player2_score;
            $opponentScore = $isPlayer1 ? $match->player2_score : $match->player1_score;

            $goalsFor += $playerScore;
            $goalsAgainst += $opponentScore;

            if ($playerScore > $opponentScore) {
                $points += 3;
                $wins++;
            } elseif ($playerScore == $opponentScore) {
                $points += 1;
                $draws++;
            } else {
                $losses++;
            }
        }

        $standings[] = [
            'user' => $participant->user,
            'played' => $matches->count(),
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'goal_difference' => $goalsFor - $goalsAgainst,
            'points' => $points
        ];
    }

    // Trier par points DESC, puis différence de buts DESC
    usort($standings, function($a, $b) {
        if ($a['points'] != $b['points']) {
            return $b['points'] - $a['points'];
        }
        return $b['goal_difference'] - $a['goal_difference'];
    });

    return $standings;
}
```

---

### 4.4 Système de Wallet (Porte-monnaie)

#### 4.4.1 Recharge de Solde

**Workflow** :

1. **Joueur demande une recharge**
   - Montant souhaité en coins (ex: 100 coins = 1000 FCFA)
   - Choix de la méthode de paiement (Orange Money, MTN Money, carte bancaire)

2. **Initialisation du paiement**
   ```php
   function initiateDeposit(User $user, float $amount, string $paymentMethod) {
       // Créer une transaction en attente
       $transaction = WalletTransaction::create([
           'user_id' => $user->id,
           'type' => 'deposit',
           'amount' => $amount,
           'balance_before' => $user->wallet_balance,
           'balance_after' => $user->wallet_balance, // Pas encore crédité
           'status' => 'pending',
           'payment_method' => $paymentMethod,
           'description' => "Recharge de $amount coins"
       ]);

       // Appeler l'API de paiement externe (ex: CinetPay, Fedapay)
       $paymentGateway = PaymentGateway::init($paymentMethod);
       $paymentResponse = $paymentGateway->initiate([
           'amount' => $amount * 10, // Convertir en FCFA
           'currency' => 'XOF',
           'transaction_id' => $transaction->id,
           'callback_url' => route('payment.callback')
       ]);

       $transaction->update([
           'payment_reference' => $paymentResponse['reference']
       ]);

       return $paymentResponse['payment_url'];
   }
   ```

3. **Callback de paiement** (webhook du gateway)
   ```php
   function handlePaymentCallback(Request $request) {
       $reference = $request->input('reference');
       $status = $request->input('status'); // 'success' ou 'failed'

       $transaction = WalletTransaction::where('payment_reference', $reference)->first();

       if ($status === 'success') {
           // Créditer le solde
           $user = $transaction->user;
           $user->wallet_balance += $transaction->amount;
           $user->total_earned += $transaction->amount;
           $user->save();

           $transaction->update([
               'status' => 'completed',
               'balance_after' => $user->wallet_balance
           ]);

           // Notification
           $user->notify(new DepositSuccessNotification($transaction));
       } else {
           $transaction->update(['status' => 'failed']);
           $user->notify(new DepositFailedNotification($transaction));
       }
   }
   ```

---

#### 4.4.2 Retrait de Fonds

**Workflow** :

1. **Joueur demande un retrait**
   - Montant à retirer (min 10 coins = 100 FCFA, max 1000 coins/jour)
   - Numéro de mobile money
   - Validation : solde suffisant, compte vérifié

2. **Création de la demande**
   ```php
   function requestWithdrawal(User $user, float $amount, string $phoneNumber, string $paymentMethod) {
       // Validations
       if ($amount < 10) {
           throw new ValidationException('Montant minimum : 10 coins');
       }

       if ($user->wallet_balance < $amount) {
           throw new ValidationException('Solde insuffisant');
       }

       if (!$user->is_verified) {
           throw new ValidationException('Compte non vérifié');
       }

       // Débiter immédiatement (en attente de traitement)
       $transaction = WalletTransaction::create([
           'user_id' => $user->id,
           'type' => 'withdrawal',
           'amount' => -$amount,
           'balance_before' => $user->wallet_balance,
           'balance_after' => $user->wallet_balance - $amount,
           'status' => 'pending',
           'payment_method' => $paymentMethod,
           'description' => "Retrait de $amount coins vers $phoneNumber"
       ]);

       $user->wallet_balance -= $amount;
       $user->save();

       // Créer la demande de retrait
       $withdrawalRequest = WithdrawalRequest::create([
           'user_id' => $user->id,
           'amount' => $amount,
           'amount_fcfa' => $amount * 10,
           'phone_number' => $phoneNumber,
           'payment_method' => $paymentMethod,
           'status' => 'pending',
           'transaction_id' => $transaction->id
       ]);

       // Notifier les admins
       Admin::notifyAll(new WithdrawalRequestNotification($withdrawalRequest));

       return $withdrawalRequest;
   }
   ```

3. **Traitement par l'admin**
   ```php
   function processWithdrawal(WithdrawalRequest $request, User $admin) {
       $request->update([
           'status' => 'processing',
           'processed_by' => $admin->id
       ]);

       // Effectuer le paiement mobile money
       $paymentGateway = PaymentGateway::init($request->payment_method);
       $result = $paymentGateway->sendMoney([
           'amount' => $request->amount_fcfa,
           'phone_number' => $request->phone_number,
           'description' => "Retrait MLM - {$request->user->username}"
       ]);

       if ($result['success']) {
           $request->update([
               'status' => 'completed',
               'processed_at' => now()
           ]);

           $request->transaction->update(['status' => 'completed']);

           $request->user->notify(new WithdrawalCompletedNotification($request));
       } else {
           // Échec : rembourser le joueur
           $request->update([
               'status' => 'rejected',
               'rejection_reason' => $result['error']
           ]);

           $user = $request->user;
           $user->wallet_balance += $request->amount; // Rembourser
           $user->save();

           $request->transaction->update(['status' => 'failed']);

           $user->notify(new WithdrawalRejectedNotification($request));
       }
   }
   ```

---

### 4.5 Tournois Payants

#### 4.5.1 Création d'un Tournoi Payant

**Workflow Organisateur** :

```php
function createPaidTournament(User $organizer, array $data) {
    $tournament = Tournament::create([
        'organizer_id' => $organizer->id,
        'name' => $data['name'],
        'game_id' => $data['game_id'],
        'type' => $data['type'],
        'format' => $data['format'],
        'max_players' => $data['max_players'],
        'is_paid' => true,
        'entry_fee' => $data['entry_fee'], // ex: 20 coins
        'platform_fee_percentage' => 10, // 10% pour MLM
        'organizer_fee_percentage' => 5,  // 5% pour l'organisateur
        'prize_distribution' => $data['prize_distribution'] // ex: {"1": 50, "2": 30, "3-4": 10}
    ]);

    // Calculer la cagnotte
    $totalCollected = $tournament->entry_fee * $tournament->max_players;
    $platformFee = $totalCollected * ($tournament->platform_fee_percentage / 100);
    $organizerFee = $totalCollected * ($tournament->organizer_fee_percentage / 100);
    $prizePool = $totalCollected - $platformFee - $organizerFee;

    $tournament->update(['prize_pool' => $prizePool]);

    return $tournament;
}
```

**Exemple de calcul** :
- Entry fee : 20 coins
- 8 joueurs
- Total collecté : 160 coins
- Frais plateforme (10%) : 16 coins
- Frais organisateur (5%) : 8 coins
- Cagnotte : 136 coins

Répartition (50% / 30% / 20%) :
- 1er : 68 coins
- 2ème : 40.8 coins
- 3-4ème : 13.6 coins chacun

---

#### 4.5.2 Inscription à un Tournoi Payant

**Workflow** :

```php
function joinPaidTournament(User $user, Tournament $tournament) {
    // Vérifier le solde
    if ($user->wallet_balance < $tournament->entry_fee) {
        throw new InsufficientBalanceException(
            "Solde insuffisant. Requis : {$tournament->entry_fee} coins, Disponible : {$user->wallet_balance} coins"
        );
    }

    // Débiter les frais d'inscription
    $transaction = WalletTransaction::create([
        'user_id' => $user->id,
        'type' => 'tournament_entry',
        'amount' => -$tournament->entry_fee,
        'balance_before' => $user->wallet_balance,
        'balance_after' => $user->wallet_balance - $tournament->entry_fee,
        'status' => 'completed',
        'tournament_id' => $tournament->id,
        'description' => "Inscription au tournoi : {$tournament->name}"
    ]);

    $user->wallet_balance -= $tournament->entry_fee;
    $user->total_spent += $tournament->entry_fee;
    $user->save();

    // Créer le participant
    $participant = TournamentParticipant::create([
        'tournament_id' => $tournament->id,
        'user_id' => $user->id,
        'elo_before' => $user->mlm_rank
    ]);

    $tournament->increment('current_players_count');

    return $participant;
}
```

---

#### 4.5.3 Distribution des Gains

**Workflow (en fin de tournoi)** :

```php
function distributePrizes(Tournament $tournament) {
    if (!$tournament->is_paid) {
        return; // Tournoi gratuit
    }

    $prizeDistribution = $tournament->prize_distribution;
    $participants = $tournament->participants;

    foreach ($prizeDistribution as $position => $percentage) {
        $prizeAmount = ($tournament->prize_pool * $percentage) / 100;

        if (str_contains($position, '-')) {
            // Ex: "3-4" = 10% chacun
            [$start, $end] = explode('-', $position);
            $winners = $participants->whereBetween('final_position', [$start, $end]);
            $prizePerWinner = $prizeAmount / $winners->count();

            foreach ($winners as $winner) {
                creditWinner($winner->user, $prizePerWinner, $tournament);
            }
        } else {
            // Ex: "1" = 50%
            $winner = $participants->where('final_position', $position)->first();
            if ($winner) {
                creditWinner($winner->user, $prizeAmount, $tournament);
            }
        }
    }

    // Créditer l'organisateur
    $organizerEarnings = ($tournament->entry_fee * $tournament->max_players * $tournament->organizer_fee_percentage) / 100;
    if ($organizerEarnings > 0) {
        creditOrganizer($tournament->organizer, $organizerEarnings, $tournament);
    }
}

function creditWinner(User $user, float $amount, Tournament $tournament) {
    $transaction = WalletTransaction::create([
        'user_id' => $user->id,
        'type' => 'tournament_win',
        'amount' => $amount,
        'balance_before' => $user->wallet_balance,
        'balance_after' => $user->wallet_balance + $amount,
        'status' => 'completed',
        'tournament_id' => $tournament->id,
        'description' => "Gain du tournoi : {$tournament->name}"
    ]);

    $user->wallet_balance += $amount;
    $user->total_earned += $amount;
    $user->save();

    $user->notify(new PrizeWonNotification($tournament, $amount));
}
```

---

#### 4.5.4 Remboursement (Tournoi Annulé)

```php
function refundTournament(Tournament $tournament) {
    $tournament->update(['status' => 'cancelled']);

    $participants = $tournament->participants;

    foreach ($participants as $participant) {
        // Rembourser les frais d'inscription
        $transaction = WalletTransaction::create([
            'user_id' => $participant->user_id,
            'type' => 'refund',
            'amount' => $tournament->entry_fee,
            'balance_before' => $participant->user->wallet_balance,
            'balance_after' => $participant->user->wallet_balance + $tournament->entry_fee,
            'status' => 'completed',
            'tournament_id' => $tournament->id,
            'description' => "Remboursement : {$tournament->name} (annulé)"
        ]);

        $participant->user->wallet_balance += $tournament->entry_fee;
        $participant->user->save();

        $participant->user->notify(new TournamentRefundedNotification($tournament));
    }
}
```

---

### 4.6 Système de Divisions Automatiques

#### 4.6.1 Rejoindre une Division

**Workflow** :

```php
function joinDivision(User $user, Division $division) {
    // Vérifications
    if ($user->mlm_rank < $division->min_mlm_rank || $user->mlm_rank > $division->max_mlm_rank) {
        throw new ValidationException("Votre MLM Rank ({$user->mlm_rank}) ne correspond pas à cette division.");
    }

    if ($division->current_members_count >= $division->max_members) {
        throw new ValidationException("Cette division est complète.");
    }

    if ($user->wallet_balance < $division->entry_fee) {
        throw new InsufficientBalanceException("Solde insuffisant pour rejoindre cette division.");
    }

    // Débiter les frais
    $transaction = WalletTransaction::create([
        'user_id' => $user->id,
        'type' => 'tournament_entry',
        'amount' => -$division->entry_fee,
        'balance_before' => $user->wallet_balance,
        'balance_after' => $user->wallet_balance - $division->entry_fee,
        'status' => 'completed',
        'description' => "Accès à la division : {$division->name}"
    ]);

    $user->wallet_balance -= $division->entry_fee;
    $user->current_division_id = $division->id;
    $user->save();

    // Créer l'adhésion
    $membership = DivisionMembership::create([
        'user_id' => $user->id,
        'division_id' => $division->id,
        'status' => 'active',
        'season_points' => 0
    ]);

    $division->increment('current_members_count');

    return $membership;
}
```

---

#### 4.6.2 Génération Automatique des Tournois de Division

**Workflow (Cron Job)** :

```php
// Exécuté selon la fréquence définie (daily, weekly, monthly)
function generateDivisionTournaments() {
    $divisions = Division::where('is_active', true)->get();

    foreach ($divisions as $division) {
        $shouldGenerate = false;

        switch ($division->tournament_frequency) {
            case 'daily':
                $shouldGenerate = true; // Tous les jours à minuit
                break;
            case 'weekly':
                $shouldGenerate = now()->dayOfWeek === 1; // Tous les lundis
                break;
            case 'monthly':
                $shouldGenerate = now()->day === 1; // Premier du mois
                break;
        }

        if ($shouldGenerate) {
            createDivisionTournament($division);
        }
    }
}

function createDivisionTournament(Division $division) {
    // Récupérer les membres actifs
    $members = $division->memberships()
        ->where('status', 'active')
        ->limit($division->tournament_size)
        ->orderBy('rank_in_division')
        ->get();

    if ($members->count() < $division->tournament_size) {
        return; // Pas assez de joueurs
    }

    // Créer le tournoi
    $tournament = Tournament::create([
        'organizer_id' => 1, // Système
        'game_id' => $division->game_id,
        'division_id' => $division->id,
        'name' => "{$division->name} - " . now()->format('d/m/Y'),
        'type' => $division->tournament_format,
        'format' => $division->tournament_size,
        'max_players' => $division->tournament_size,
        'is_paid' => false, // Gratuit (frais déjà payés à l'adhésion)
        'is_division_tournament' => true,
        'is_public' => false
    ]);

    // Inscrire les joueurs automatiquement
    foreach ($members as $member) {
        TournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'user_id' => $member->user_id,
            'elo_before' => $member->user->mlm_rank
        ]);

        $tournament->increment('current_players_count');
    }

    // Démarrer automatiquement
    $tournament->update(['status' => 'ready']);

    // Notifier tous les participants
    foreach ($members as $member) {
        $member->user->notify(new DivisionTournamentStartedNotification($tournament));
    }
}
```

---

#### 4.6.3 Promotion et Relégation

**Workflow (Fin de Saison)** :

```php
function processSeasonEnd(Division $division) {
    $memberships = $division->memberships()
        ->where('status', 'active')
        ->orderBy('season_points', 'desc')
        ->get();

    // Promotion (top X joueurs)
    $promotedMembers = $memberships->take($division->promotion_count);
    $upperDivision = Division::where('level', $division->level - 1)->first();

    if ($upperDivision) {
        foreach ($promotedMembers as $member) {
            promotePlayer($member, $upperDivision);
        }
    }

    // Relégation (bottom X joueurs)
    $relegatedMembers = $memberships->slice(-$division->relegation_count);
    $lowerDivision = Division::where('level', $division->level + 1)->first();

    if ($lowerDivision) {
        foreach ($relegatedMembers as $member) {
            relegatePlayer($member, $lowerDivision);
        }
    }

    // Réinitialiser les points de saison
    $division->memberships()->update([
        'season_points' => 0,
        'season_wins' => 0,
        'season_losses' => 0
    ]);
}

function promotePlayer(DivisionMembership $membership, Division $newDivision) {
    $membership->update(['status' => 'inactive', 'left_at' => now()]);

    DivisionMembership::create([
        'user_id' => $membership->user_id,
        'division_id' => $newDivision->id,
        'status' => 'active',
        'season_points' => 0
    ]);

    $membership->user->update(['current_division_id' => $newDivision->id]);
    $membership->user->notify(new PromotedNotification($newDivision));
}

function relegatePlayer(DivisionMembership $membership, Division $newDivision) {
    $membership->update(['status' => 'inactive', 'left_at' => now()]);

    DivisionMembership::create([
        'user_id' => $membership->user_id,
        'division_id' => $newDivision->id,
        'status' => 'active',
        'season_points' => 0
    ]);

    $membership->user->update(['current_division_id' => $newDivision->id]);
    $membership->user->notify(new RelegatedNotification($newDivision));
}
```

---

## 5. Endpoints API

### 5.1 Authentification

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/api/register` | Inscription | Non |
| POST | `/api/login` | Connexion | Non |
| POST | `/api/logout` | Déconnexion | Oui |
| GET | `/api/me` | Profil utilisateur | Oui |
| PUT | `/api/me` | Mettre à jour profil | Oui |

### 5.2 Tournois

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/tournaments` | Liste des tournois publics | Non |
| GET | `/api/tournaments/{id}` | Détails d'un tournoi | Non |
| POST | `/api/tournaments` | Créer un tournoi | Oui |
| PUT | `/api/tournaments/{id}` | Modifier (organisateur) | Oui |
| DELETE | `/api/tournaments/{id}` | Supprimer (organisateur) | Oui |
| POST | `/api/tournaments/{id}/join` | S'inscrire | Oui |
| POST | `/api/tournaments/{id}/leave` | Se désinscrire | Oui |
| POST | `/api/tournaments/{id}/start` | Démarrer (organisateur) | Oui |
| GET | `/api/tournaments/{id}/bracket` | Voir le bracket | Non |
| GET | `/api/tournaments/{id}/standings` | Classement (league) | Non |

### 5.3 Matchs

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/matches/{id}` | Détails d'un match | Non |
| POST | `/api/matches/{id}/declare-score` | Déclarer un score | Oui |
| GET | `/api/matches/{id}/declarations` | Voir les déclarations | Oui |

### 5.4 Litiges

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/disputes` | Litiges d'un organisateur | Oui |
| GET | `/api/disputes/{id}` | Détails d'un litige | Oui |
| POST | `/api/disputes/{id}/resolve` | Résoudre (organisateur) | Oui |

### 5.5 Messages/Chat

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/tournaments/{id}/messages` | Messages du tournoi | Oui |
| POST | `/api/tournaments/{id}/messages` | Envoyer un message | Oui |

### 5.6 Classement Global

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/rankings` | Top MLM Rank | Non |
| GET | `/api/users/{id}` | Profil public | Non |
| GET | `/api/users/{id}/history` | Historique tournois | Non |

---

## 6. Système de Notifications

### 6.1 Types de Notifications

| Type | Déclencheur | Destinataire |
|------|-------------|--------------|
| `tournament_full` | Inscriptions complètes | Tous les participants |
| `tournament_started` | Tournoi démarré | Tous les participants |
| `match_ready` | Match prêt à jouer | Les 2 joueurs |
| `opponent_declared` | Adversaire a déclaré | L'autre joueur |
| `match_validated` | Score validé | Les 2 joueurs |
| `deadline_reminder` | 24h/12h/6h avant deadline | Joueur n'ayant pas déclaré |
| `dispute_created` | Litige détecté | Organisateur |
| `dispute_resolved` | Litige résolu | Les 2 joueurs |
| `promoted_next_round` | Victoire, passage au tour suivant | Vainqueur |
| `eliminated` | Défaite | Perdant |
| `tournament_completed` | Tournoi terminé | Tous les participants |

### 6.2 Canaux de Notification

- **Push Notifications** : Firebase Cloud Messaging (FCM)
- **In-App** : Badge + liste dans l'app
- **Email** : Optionnel (pour événements importants)

---

## 7. Sécurité et Permissions

### 7.1 Autorisations (Policies)

**Tournoi** :
- **Créer** : Tout utilisateur authentifié
- **Modifier** : Organisateur uniquement
- **Supprimer** : Organisateur (seulement si `status == 'registration'`)
- **Démarrer** : Organisateur (si `status == 'ready'`)
- **Voir** : Public (si `is_public == true`), sinon participants uniquement

**Match** :
- **Déclarer score** : Seulement `player1_id` ou `player2_id`
- **Voir déclarations** : Les 2 joueurs + organisateur

**Litige** :
- **Résoudre** : Organisateur uniquement

### 7.2 Validation des Données

**Création de Tournoi** :
- `name` : Requis, 3-100 caractères
- `max_players` : Requis, doit être 8, 16, ou 32 (pour knockout)
- `type` : Requis, enum ('knockout', 'league')
- `match_deadline_hours` : Optionnel, défaut 24, min 6, max 168 (7 jours)

**Déclaration de Score** :
- `player1_score` : Requis, entier >= 0
- `player2_score` : Requis, entier >= 0
- `proof_url` : Requis, URL valide

### 7.3 Protection contre la Triche

1. **Upload de Preuves Obligatoire**
   - Impossible de valider un score sans capture d'écran

2. **Détection de Patterns Suspects**
   - Joueurs déclarant systématiquement des scores opposés
   - Historique de litiges (flag si > 30% des matchs)

3. **Limitation des Actions**
   - Rate limiting sur les endpoints sensibles (déclaration de score)
   - Impossibilité de modifier une déclaration après validation

---

## 8. Questions et Décisions en Suspens

### 8.1 Gestion du Bracket

❓ **Question 1 : Joueurs non-puissance de 2**

**Contexte** : Si un organisateur veut un tournoi de 10 joueurs, que fait-on ?

**Options** :
- **A** : Bloquer complètement (accepter seulement 8, 16, 32)
- **B** : Autoriser et générer des "byes" (certains joueurs passent automatiquement au tour suivant)
- **C** : Permettre à l'organisateur de choisir (flexible)

**Recommandation** : Option A (simple) pour v1, Option B pour v2

---

❓ **Question 2 : Détermination du Seeding**

**Contexte** : Comment choisir les têtes de série ?

**Options** :
- **A** : Par MLM Rank (meilleur joueur = seed 1)
- **B** : Aléatoire complet (shuffle)
- **C** : Choix de l'organisateur (drag & drop manuel)

**Recommandation** : Option A par défaut, avec option B si l'organisateur coche "Seeding aléatoire"

---

### 8.2 Validation des Scores

❓ **Question 3 : Preuves Obligatoires ou Optionnelles ?**

**Contexte** : Doit-on forcer l'upload de capture d'écran ?

**Options** :
- **A** : Obligatoire (plus sécurisé, mais friction)
- **B** : Optionnel (plus rapide, mais risque de litiges)
- **C** : Obligatoire seulement pour tournois > 8 joueurs

**Recommandation** : Option A (obligatoire) pour garantir le fair-play

---

❓ **Question 4 : Timeout de Déclaration**

**Contexte** : Combien de temps après le match pour déclarer ?

**Options** :
- **A** : Délai fixe global (ex: 24h)
- **B** : Configurable par tournoi (organisateur choisit)
- **C** : Pas de délai (attente infinie)

**Recommandation** : Option B (configurable, défaut 24h)

---

❓ **Question 5 : Forfait Automatique ou Arbitrage ?**

**Contexte** : Si seul un joueur déclare et le délai expire, que faire ?

**Options** :
- **A** : Validation automatique du score déclaré
- **B** : Notification à l'organisateur qui décide
- **C** : Victoire automatique par forfait (3-0 au joueur ayant déclaré)

**Recommandation** : Option B (notification organisateur) pour v1

---

### 8.3 Classement ELO

❓ **Question 6 : Pondération du Calcul ELO**

**Contexte** : Comment valoriser un tournoi de 32 joueurs vs 8 joueurs ?

**Options** :
- **A** : Multiplicateur linéaire (8 = 1x, 16 = 1.5x, 32 = 2x)
- **B** : Multiplicateur logarithmique
- **C** : Pas de pondération (même gain/perte peu importe la taille)

**Recommandation** : Option A pour v1 (simple et intuitif)

---

❓ **Question 7 : Bonus de Tour**

**Contexte** : Doit-on donner des points bonus selon le tour atteint ?

**Options** :
- **A** : Oui (ex: +10 demi, +20 finale, +50 victoire)
- **B** : Non (seulement formule ELO standard)
- **C** : Oui, mais variable selon taille du tournoi

**Recommandation** : Option A (encourage la participation et valorise les bonnes performances)

---

❓ **Question 8 : Tournois Abandonnés**

**Contexte** : Si un tournoi n'est jamais terminé, que faire des points ELO ?

**Options** :
- **A** : Annuler tous les changements ELO
- **B** : Conserver les changements des matchs validés
- **C** : Pénalité pour tous les participants (-X points)

**Recommandation** : Option A si `status == 'cancelled'`, Option B si simplement inactif

---

### 8.4 Ligues (Round Robin)

❓ **Question 9 : Support des Nuls**

**Contexte** : Les jeux mobiles de foot permettent les matchs nuls, faut-il les gérer ?

**Options** :
- **A** : Oui (score identique = nul = 1pt chacun)
- **B** : Non (forcer les prolongations/penalties = toujours un vainqueur)
- **C** : Laisser l'organisateur choisir

**Recommandation** : Option A (réaliste, simplifie la validation)

---

❓ **Question 10 : Calendrier Automatique ou Manuel ?**

**Contexte** : Qui génère les dates des matchs ?

**Options** :
- **A** : Automatique (1 journée par semaine)
- **B** : Manuel (organisateur définit chaque date)
- **C** : Suggéré automatiquement mais modifiable

**Recommandation** : Option C (flexibilité max)

---

### 8.5 Chat et Communication

❓ **Question 11 : Chat Global ou par Match ?**

**Contexte** : Portée du système de messagerie

**Options** :
- **A** : Un seul chat global par tournoi
- **B** : Un chat par match (seulement les 2 joueurs)
- **C** : Les deux (chat tournoi + chat match)

**Recommandation** : Option A pour v1 (simple), Option C pour v2

---

## 9. Prochaines Étapes

### Phase 1 : Analyse et Validation
- [ ] Valider les décisions en suspens (Section 8)
- [ ] Confirmer le modèle de données final
- [ ] Valider les règles métier critiques

### Phase 2 : Conception Détaillée
- [ ] Diagrammes UML (classes, séquences)
- [ ] Schéma de base de données finalisé
- [ ] Spécifications API complètes (Swagger)

### Phase 3 : Développement (Après validation)
- [ ] Migrations Flyway/Liquibase
- [ ] Entités JPA + Relations (Hibernate)
- [ ] Seeders et Data Initialization
- [ ] Controllers REST et Endpoints
- [ ] Logic Services (BracketGenerator, EloCalculator, etc.)
- [ ] Tests unitaires
- [ ] Tests d'intégration

---

**Document vivant** : Ce cahier des charges sera mis à jour au fur et à mesure des décisions prises et de l'évolution du projet.

**Contributeurs** : Toute suggestion d'amélioration est la bienvenue via Issues/PRs GitHub.
