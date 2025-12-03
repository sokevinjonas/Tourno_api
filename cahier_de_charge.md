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
│   LOGIQUE MÉTIER (Laravel API)          │
│   - Générateur de Bracket               │
│   - Moteur de Validation                │
│   - Calculateur ELO                     │
│   - Gestionnaire de Notifications       │
└─────────────────┬───────────────────────┘
                  │ Eloquent ORM
┌─────────────────▼───────────────────────┐
│   COUCHE DONNÉES (MySQL/PostgreSQL)     │
│   - Persistance                         │
│   - Intégrité référentielle             │
│   - Historique                          │
└─────────────────────────────────────────┘
```

### 2.2 Stack Technique

**Backend**
- Framework : Laravel 11.x
- Langage : PHP 8.2+
- API : RESTful
- Authentification : Laravel Sanctum (token-based)
- Temps réel : Laravel Broadcasting (Pusher/Socket.io)
- Queue : Redis + Laravel Queue
- Cache : Redis

**Base de Données**
- Primaire : MySQL 8.0+ / PostgreSQL 14+
- Schema migrations : Laravel Migrations
- Seeders : Faker pour données de test

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
├── avatar_url
├── mlm_rank (points ELO global)
├── total_tournaments_played
├── total_wins
├── total_losses
├── win_rate (calculé)
├── created_at
└── updated_at
```

**Règles de gestion** :
- `username` : 3-20 caractères, alphanumérique + underscore
- `mlm_rank` : Initialisation à 1000 points pour tout nouveau joueur
- `win_rate` : Calculé automatiquement (total_wins / total_tournaments_played)

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

#### 3.1.3 Tournaments (Tournois)

```
tournaments
├── id (PK)
├── organizer_id (FK -> users.id)
├── game_id (FK -> games.id)
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
├── prize_description
├── is_public (boolean)
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

### 3.2 Relations entre Entités

```
Users (1) ──────< Tournaments (organizer_id)
Users (1) ──────< Tournament_Participants
Users (1) ──────< Matches (player1_id, player2_id, winner_id)
Users (1) ──────< Score_Declarations
Users (1) ──────< Tournament_Messages
Users (1) ──────< Notifications

Games (1) ──────< Tournaments

Tournaments (1) ──────< Tournament_Participants
Tournaments (1) ──────< Rounds
Tournaments (1) ──────< Matches
Tournaments (1) ──────< Disputes
Tournaments (1) ──────< Tournament_Messages

Rounds (1) ──────< Matches

Matches (1) ──────< Score_Declarations
Matches (1) ──────< Disputes
Matches (1) ──────< Matches (next_match_id, auto-référence)
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
- [ ] Migrations Laravel
- [ ] Modèles Eloquent + Relations
- [ ] Seeders et Factories
- [ ] Controllers et Routes
- [ ] Logic Services (BracketGenerator, EloCalculator, etc.)
- [ ] Tests unitaires
- [ ] Tests d'intégration

---

**Document vivant** : Ce cahier des charges sera mis à jour au fur et à mesure des décisions prises et de l'évolution du projet.

**Contributeurs** : Toute suggestion d'amélioration est la bienvenue via Issues/PRs GitHub.
