# 🗄️ SCHÉMA DE BASE DE DONNÉES - MVP

## 📋 Vue d'ensemble

Ce document décrit le schéma de base de données complet pour le MVP de Mobile League Manager (MLM).

---

## 📊 Diagramme ERD (Relations)

```
┌─────────────────┐
│  login_tokens   │  (Magic Links - pas de FK vers users)
└─────────────────┘

┌─────────────┐         ┌──────────────────┐
│    users    │────1:N──│ oauth_providers  │  (Google, Apple, Facebook)
└─────────────┘         └──────────────────┘
       │
       │ 1:1
       ▼
┌──────────────┐         ┌─────────────────┐
│   profiles   │────1:N──│  game_accounts  │
└──────────────┘         └─────────────────┘

┌─────────────┐         ┌──────────────────┐
│    users    │────1:1──│     wallets      │────1:N──│   transactions   │
└─────────────┘         └──────────────────┘         └──────────────────┘
       │
       │ (user_id)
       │
┌──────────────┐        ┌────────────────────────┐
│ tournaments  │───1:N──│ tournament_registrations│
└──────────────┘        └────────────────────────┘
       │                          │
       │ 1:N                      │ N:1 (user_id)
       ▼                          │
┌──────────────┐                  │
│    rounds    │                  │
└──────────────┘                  │
       │                          │
       │ 1:N                      │
       ▼                          │
┌──────────────┐                  │
│   matches    │──────────────────┘
└──────────────┘
       │
       │ 1:N
       ▼
┌────────────────┐
│ match_results  │
└────────────────┘
```

---

## 🗂️ Tables Détaillées

### 1. **users** - Utilisateurs

Stocke les informations d'authentification et le rôle de l'utilisateur.

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('avatar_url')->nullable(); // Photo de profil (depuis OAuth ou upload)
    $table->enum('role', ['admin', 'moderator', 'organizer', 'player'])->default('player');
    $table->boolean('is_banned')->default(false);
    $table->timestamp('banned_until')->nullable();
    $table->text('ban_reason')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

**Champs** :
- `id` : ID unique
- `name` : Nom complet
- `email` : Email (unique)
- `email_verified_at` : Date de vérification email
- `avatar_url` : URL de la photo de profil (depuis OAuth ou upload manuel)
- `role` : Rôle (admin, moderator, organizer, player)
- `is_banned` : Utilisateur banni ou non
- `banned_until` : Date de fin du ban (si temporaire)
- `ban_reason` : Raison du bannissement
- `timestamps` : created_at, updated_at
- `softDeletes` : deleted_at (suppression douce)

**Index** :
- `email` (unique)
- `role`
- `is_banned`

**Notes** :
- ❌ **Pas de champ `password`** : Authentification via OAuth ou Magic Link uniquement
- ✅ Email automatiquement vérifié pour OAuth
- ✅ Email vérifié manuellement pour Magic Link

---

### 2. **oauth_providers** - Fournisseurs OAuth

Stocke les connexions des utilisateurs via les fournisseurs OAuth (Google, Apple, Facebook).

```php
Schema::create('oauth_providers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('provider', ['google', 'apple', 'facebook']);
    $table->string('provider_user_id'); // ID unique chez le fournisseur
    $table->string('provider_email')->nullable();
    $table->text('access_token')->nullable(); // Token d'accès OAuth
    $table->text('refresh_token')->nullable(); // Token de rafraîchissement
    $table->timestamp('token_expires_at')->nullable();
    $table->timestamps();

    $table->unique(['provider', 'provider_user_id']); // Un compte OAuth ne peut être lié qu'une fois
});
```

**Champs** :
- `id` : ID unique
- `user_id` : Référence à users (CASCADE)
- `provider` : Fournisseur (google, apple, facebook)
- `provider_user_id` : ID unique de l'utilisateur chez le fournisseur
- `provider_email` : Email fourni par le provider (peut différer de l'email principal)
- `access_token` : Token d'accès OAuth (chiffré)
- `refresh_token` : Token de rafraîchissement (chiffré)
- `token_expires_at` : Date d'expiration du token
- `timestamps` : created_at, updated_at

**Index** :
- `user_id`
- Unique composite : (`provider`, `provider_user_id`)

**Relations** :
- `user` : BelongsTo User

**Note** : Un utilisateur peut lier plusieurs providers (ex: Google + Facebook)

---

### 3. **login_tokens** - Tokens de Connexion (Magic Links)

Stocke les tokens pour l'authentification par Magic Link.

```php
Schema::create('login_tokens', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    $table->string('token', 64)->unique(); // Token unique généré
    $table->boolean('is_used')->default(false);
    $table->timestamp('expires_at');
    $table->ipAddress('ip_address')->nullable(); // IP de la requête
    $table->text('user_agent')->nullable(); // User agent du navigateur
    $table->timestamp('used_at')->nullable();
    $table->timestamps();

    $table->index('token');
    $table->index('email');
    $table->index('expires_at');
});
```

**Champs** :
- `id` : ID unique
- `email` : Email de l'utilisateur demandant la connexion
- `token` : Token unique généré (64 caractères aléatoires)
- `is_used` : Token déjà utilisé ou non
- `expires_at` : Date d'expiration (ex: 15 minutes après création)
- `ip_address` : IP de l'utilisateur qui a demandé le token (sécurité)
- `user_agent` : User agent du navigateur (sécurité)
- `used_at` : Date d'utilisation du token
- `timestamps` : created_at, updated_at

**Index** :
- `token` (unique)
- `email`
- `expires_at`

**Workflow Magic Link** :
1. Utilisateur entre son email
2. Système génère un token unique
3. Email envoyé avec lien : `https://mlm.app/auth/verify?token=XXXXXX`
4. Utilisateur clique → Vérification du token
5. Si valide et non expiré → Connexion automatique
6. Token marqué comme `is_used = true`

**Sécurité** :
- Token expire après 15 minutes
- Token à usage unique
- Nettoyage automatique des tokens expirés (tâche cron)

---

### 5. **profiles** - Profils Utilisateurs

Stocke les informations personnelles des joueurs.

```php
Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('whatsapp_number');
    $table->string('country');
    $table->string('city');
    $table->enum('status', ['pending', 'validated', 'rejected'])->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('validated_at')->nullable();
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `user_id` : Référence à users (CASCADE)
- `whatsapp_number` : Numéro WhatsApp
- `country` : Pays
- `city` : Ville
- `status` : Statut (pending, validated, rejected)
- `rejection_reason` : Raison du rejet (si rejeté)
- `validated_by` : ID du modérateur qui a validé
- `validated_at` : Date de validation
- `timestamps` : created_at, updated_at

**Index** :
- `user_id` (unique)
- `status`

**Relations** :
- `user` : BelongsTo User
- `validator` : BelongsTo User (validated_by)

---

### 6. **game_accounts** - Comptes de Jeu

Stocke les pseudos et screenshots pour chaque jeu pratiqué.

```php
Schema::create('game_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('game', ['efootball', 'fc_mobile', 'dream_league_soccer']);
    $table->string('game_username'); // Pseudo dans le jeu
    $table->string('team_screenshot_path'); // Chemin vers screenshot de l'équipe
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `user_id` : Référence à users (CASCADE)
- `game` : Jeu (efootball, fc_mobile, dream_league_soccer)
- `game_username` : Pseudo dans le jeu
- `team_screenshot_path` : Chemin du screenshot (ex: /storage/screenshots/user_123_efootball.png)
- `timestamps` : created_at, updated_at

**Index** :
- `user_id`
- `game`
- Composite unique : (`user_id`, `game`) - Un joueur ne peut avoir qu'un seul compte par jeu

**Relations** :
- `user` : BelongsTo User

---

### 7. **wallets** - Portefeuilles

Stocke le solde en pièces MLM de chaque utilisateur.

```php
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->decimal('balance', 10, 2)->default(0.00); // En pièces MLM
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `user_id` : Référence à users (CASCADE)
- `balance` : Solde en pièces MLM (decimal pour précision)
- `timestamps` : created_at, updated_at

**Index** :
- `user_id` (unique)

**Relations** :
- `user` : BelongsTo User
- `transactions` : HasMany Transaction

**Note** : 1 pièce = 500 FCFA

---

### 8. **transactions** - Transactions

Historique de toutes les transactions (crédits, débits).

```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['credit', 'debit']);
    $table->decimal('amount', 10, 2); // Montant en pièces MLM
    $table->decimal('balance_before', 10, 2);
    $table->decimal('balance_after', 10, 2);
    $table->enum('reason', [
        'initial_bonus',           // 10 pièces après validation profil
        'tournament_registration', // Inscription tournoi
        'tournament_prize',        // Gain tournoi
        'refund',                  // Remboursement
        'admin_adjustment'         // Ajustement admin
    ]);
    $table->string('description')->nullable();
    $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('set null');
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `wallet_id` : Référence au wallet
- `user_id` : Référence à l'utilisateur (pour faciliter les requêtes)
- `type` : Type (credit, debit)
- `amount` : Montant de la transaction
- `balance_before` : Solde avant transaction
- `balance_after` : Solde après transaction
- `reason` : Raison de la transaction
- `description` : Description optionnelle
- `tournament_id` : Référence au tournoi (si applicable)
- `timestamps` : created_at, updated_at

**Index** :
- `wallet_id`
- `user_id`
- `type`
- `reason`
- `tournament_id`

**Relations** :
- `wallet` : BelongsTo Wallet
- `user` : BelongsTo User
- `tournament` : BelongsTo Tournament (nullable)

---

### 9. **tournaments** - Tournois

Stocke les informations des tournois.

```php
Schema::create('tournaments', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
    $table->enum('game', ['efootball', 'fc_mobile', 'dream_league_soccer']);
    $table->integer('max_participants');
    $table->decimal('entry_fee', 10, 2); // Frais d'inscription en pièces MLM
    $table->datetime('start_date');
    $table->enum('status', ['draft', 'open', 'in_progress', 'completed', 'cancelled'])->default('draft');
    $table->json('prize_distribution')->nullable(); // Ex: {"1": 50, "2": 30, "3": 20} en %
    $table->integer('total_rounds')->nullable(); // Calculé : ⌈log₂(P)⌉
    $table->integer('current_round')->default(0);
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `name` : Nom du tournoi
- `description` : Description
- `organizer_id` : Référence à l'organisateur (users)
- `game` : Jeu du tournoi
- `max_participants` : Nombre max de participants
- `entry_fee` : Frais d'inscription (en pièces MLM)
- `start_date` : Date de début
- `status` : Statut (draft, open, in_progress, completed, cancelled)
- `prize_distribution` : Distribution des gains en JSON (% ou montants)
- `total_rounds` : Nombre total de rondes (calculé automatiquement)
- `current_round` : Ronde actuelle
- `timestamps` : created_at, updated_at

**Index** :
- `organizer_id`
- `game`
- `status`
- `start_date`

**Relations** :
- `organizer` : BelongsTo User
- `registrations` : HasMany TournamentRegistration
- `rounds` : HasMany Round
- `matches` : HasMany Match (via rounds)

**Exemple prize_distribution** :
```json
{
  "1": 50,  // 50% du prize pool pour la 1ère place
  "2": 30,  // 30% pour la 2ème
  "3": 20   // 20% pour la 3ème
}
```

---

### 10. **tournament_registrations** - Inscriptions aux Tournois

Stocke les inscriptions des joueurs aux tournois.

```php
Schema::create('tournament_registrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('game_account_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['registered', 'withdrawn', 'disqualified'])->default('registered');
    $table->integer('tournament_points')->default(0); // Points accumulés dans le tournoi
    $table->integer('wins')->default(0);
    $table->integer('draws')->default(0);
    $table->integer('losses')->default(0);
    $table->integer('final_rank')->nullable(); // Classement final
    $table->decimal('prize_won', 10, 2)->nullable(); // Gain en pièces MLM
    $table->timestamps();

    $table->unique(['tournament_id', 'user_id']); // Un joueur ne peut s'inscrire qu'une fois
});
```

**Champs** :
- `id` : ID unique
- `tournament_id` : Référence au tournoi
- `user_id` : Référence au joueur
- `game_account_id` : Compte de jeu utilisé pour ce tournoi
- `status` : Statut (registered, withdrawn, disqualified)
- `tournament_points` : Points accumulés (3 victoire, 1 nul, 0 défaite)
- `wins` : Nombre de victoires
- `draws` : Nombre de nuls
- `losses` : Nombre de défaites
- `final_rank` : Classement final (1, 2, 3...)
- `prize_won` : Gain remporté (en pièces MLM)
- `timestamps` : created_at, updated_at

**Index** :
- `tournament_id`
- `user_id`
- Unique composite : (`tournament_id`, `user_id`)

**Relations** :
- `tournament` : BelongsTo Tournament
- `user` : BelongsTo User
- `gameAccount` : BelongsTo GameAccount

---

### 11. **rounds** - Rondes

Stocke les rondes d'un tournoi (Format Suisse).

```php
Schema::create('rounds', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
    $table->integer('round_number'); // 1, 2, 3, ...
    $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
    $table->datetime('start_date')->nullable();
    $table->datetime('end_date')->nullable();
    $table->timestamps();

    $table->unique(['tournament_id', 'round_number']);
});
```

**Champs** :
- `id` : ID unique
- `tournament_id` : Référence au tournoi
- `round_number` : Numéro de la ronde (1, 2, 3...)
- `status` : Statut (pending, in_progress, completed)
- `start_date` : Date de début de la ronde
- `end_date` : Date de fin de la ronde
- `timestamps` : created_at, updated_at

**Index** :
- `tournament_id`
- Unique composite : (`tournament_id`, `round_number`)

**Relations** :
- `tournament` : BelongsTo Tournament
- `matches` : HasMany Match

---

### 12. **matches** - Matchs

Stocke les matchs individuels.

```php
Schema::create('matches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
    $table->foreignId('round_id')->constrained()->onDelete('cascade');
    $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('player2_id')->constrained('users')->onDelete('cascade');
    $table->integer('player1_score')->nullable();
    $table->integer('player2_score')->nullable();
    $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
    $table->enum('status', ['scheduled', 'in_progress', 'pending_validation', 'completed', 'disputed'])->default('scheduled');
    $table->datetime('scheduled_at')->nullable();
    $table->datetime('completed_at')->nullable();
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `tournament_id` : Référence au tournoi
- `round_id` : Référence à la ronde
- `player1_id` : Référence au joueur 1
- `player2_id` : Référence au joueur 2
- `player1_score` : Score joueur 1 (validé)
- `player2_score` : Score joueur 2 (validé)
- `winner_id` : ID du gagnant (null si nul)
- `status` : Statut du match
  - `scheduled` : Programmé
  - `in_progress` : En cours
  - `pending_validation` : En attente de validation des résultats
  - `completed` : Terminé
  - `disputed` : Contesté
- `scheduled_at` : Date/heure programmée
- `completed_at` : Date de fin effective
- `timestamps` : created_at, updated_at

**Index** :
- `tournament_id`
- `round_id`
- `player1_id`
- `player2_id`
- `winner_id`
- `status`

**Relations** :
- `tournament` : BelongsTo Tournament
- `round` : BelongsTo Round
- `player1` : BelongsTo User
- `player2` : BelongsTo User
- `winner` : BelongsTo User (nullable)
- `results` : HasMany MatchResult

---

### 13. **match_results** - Résultats de Match

Stocke les résultats soumis par chaque joueur.

```php
Schema::create('match_results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('match_id')->constrained()->onDelete('cascade');
    $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
    $table->integer('own_score'); // Score que le joueur déclare pour lui-même
    $table->integer('opponent_score'); // Score que le joueur déclare pour l'adversaire
    $table->string('screenshot_path'); // Chemin vers le screenshot
    $table->text('comment')->nullable();
    $table->enum('status', ['pending', 'validated', 'rejected'])->default('pending');
    $table->timestamps();
});
```

**Champs** :
- `id` : ID unique
- `match_id` : Référence au match
- `submitted_by` : Joueur qui a soumis le résultat
- `own_score` : Score qu'il déclare pour lui-même
- `opponent_score` : Score qu'il déclare pour l'adversaire
- `screenshot_path` : Chemin du screenshot de preuve
- `comment` : Commentaire optionnel
- `status` : Statut (pending, validated, rejected)
- `timestamps` : created_at, updated_at

**Index** :
- `match_id`
- `submitted_by`
- `status`

**Relations** :
- `match` : BelongsTo Match
- `submittedBy` : BelongsTo User

**Logique de validation** :
- Si les 2 joueurs soumettent le même résultat → Validation automatique
- Si résultats différents → Status match = 'disputed' → L'organisateur tranche

---

## 🔗 Relations Résumées

### User
- `hasOne` Profile
- `hasOne` Wallet
- `hasMany` OAuthProvider
- `hasMany` GameAccount
- `hasMany` Transaction
- `hasMany` TournamentRegistration
- `hasMany` Tournament (as organizer)
- `hasMany` Match (as player1)
- `hasMany` Match (as player2)
- `hasMany` MatchResult (as submitter)

### OAuthProvider
- `belongsTo` User

### LoginToken
- Pas de relation Eloquent directe (recherche par email et token)

### Profile
- `belongsTo` User
- `belongsTo` User (as validator)

### GameAccount
- `belongsTo` User

### Wallet
- `belongsTo` User
- `hasMany` Transaction

### Transaction
- `belongsTo` Wallet
- `belongsTo` User
- `belongsTo` Tournament (nullable)

### Tournament
- `belongsTo` User (as organizer)
- `hasMany` TournamentRegistration
- `hasMany` Round
- `hasMany` Match

### TournamentRegistration
- `belongsTo` Tournament
- `belongsTo` User
- `belongsTo` GameAccount

### Round
- `belongsTo` Tournament
- `hasMany` Match

### Match
- `belongsTo` Tournament
- `belongsTo` Round
- `belongsTo` User (as player1)
- `belongsTo` User (as player2)
- `belongsTo` User (as winner, nullable)
- `hasMany` MatchResult

### MatchResult
- `belongsTo` Match
- `belongsTo` User (as submittedBy)

---

## 🔢 Règles Métier Importantes

### Validation de Profil
1. Profil créé → `status = 'pending'`
2. Modérateur valide → `status = 'validated'`
3. Système crée automatiquement un Wallet
4. Système crée une Transaction `initial_bonus` de 10 pièces
5. Wallet balance = 10.00

### Inscription à un Tournoi
1. Vérifier : Profil validé
2. Vérifier : Balance ≥ entry_fee
3. Créer TournamentRegistration
4. Créer Transaction `debit` de entry_fee
5. Déduire du Wallet balance

### Début de Tournoi
1. Organisateur clique "Démarrer le tournoi"
2. Calculer `total_rounds = ⌈log₂(nb_participants)⌉`
3. Créer Round 1
4. Générer les appariements (pairings aléatoires pour round 1)
5. Créer les Matchs
6. Status tournoi = 'in_progress'

### Soumission de Résultat
1. Joueur soumet résultat → Créer MatchResult
2. Si les 2 joueurs ont soumis :
   - Comparer les résultats
   - Si identiques → Valider automatiquement le Match
   - Si différents → Status match = 'disputed'
3. Mettre à jour tournament_registrations (points, wins, draws, losses)

### Fin de Ronde
1. Tous les matchs de la ronde sont completed
2. Calculer les classements (par points)
3. Si round_number < total_rounds :
   - Créer Round suivant
   - Générer nouveaux appariements (joueurs avec même score s'affrontent)
4. Si round_number == total_rounds :
   - Calculer classement final
   - Distribuer les gains
   - Status tournoi = 'completed'

### Distribution des Gains
1. Calculer prize_pool = entry_fee × nb_participants
2. Appliquer prize_distribution (ex: 50%, 30%, 20%)
3. Pour chaque gagnant :
   - Créer Transaction `credit` de type `tournament_prize`
   - Ajouter au Wallet balance
   - Mettre à jour TournamentRegistration.prize_won

---

## 🛡️ Contraintes & Validations

### Contraintes Base de Données
- Unique : email (users)
- Unique : user_id (profiles, wallets)
- Unique : (user_id, game) (game_accounts)
- Unique : (tournament_id, user_id) (tournament_registrations)
- Unique : (tournament_id, round_number) (rounds)
- Foreign keys avec CASCADE ou SET NULL selon le contexte

### Validations Applicatives (Laravel)
- Email format valide
- WhatsApp format valide
- Balance toujours ≥ 0
- Entry fee > 0
- Max participants ≥ 2
- Scores ≥ 0
- Prize distribution total = 100%

---

## 📈 Index Recommandés

Pour optimiser les performances :

```sql
-- users
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_is_banned ON users(is_banned);

-- profiles
CREATE INDEX idx_profiles_status ON profiles(status);

-- game_accounts
CREATE INDEX idx_game_accounts_user_game ON game_accounts(user_id, game);

-- transactions
CREATE INDEX idx_transactions_wallet ON transactions(wallet_id);
CREATE INDEX idx_transactions_user ON transactions(user_id);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_reason ON transactions(reason);

-- tournaments
CREATE INDEX idx_tournaments_organizer ON tournaments(organizer_id);
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_tournaments_game ON tournaments(game);
CREATE INDEX idx_tournaments_start_date ON tournaments(start_date);

-- tournament_registrations
CREATE INDEX idx_registrations_tournament ON tournament_registrations(tournament_id);
CREATE INDEX idx_registrations_user ON tournament_registrations(user_id);
CREATE INDEX idx_registrations_points ON tournament_registrations(tournament_points);

-- matches
CREATE INDEX idx_matches_tournament ON matches(tournament_id);
CREATE INDEX idx_matches_round ON matches(round_id);
CREATE INDEX idx_matches_status ON matches(status);
CREATE INDEX idx_matches_players ON matches(player1_id, player2_id);

-- match_results
CREATE INDEX idx_results_match ON match_results(match_id);
CREATE INDEX idx_results_submitted_by ON match_results(submitted_by);
```

---

## 🔐 Sécurité

### Soft Deletes
- `users` : Soft delete activé (pour historique)
- Autres tables : Cascade delete approprié

### Permissions
- Seul un modérateur peut valider un profil
- Seul l'organisateur peut gérer son tournoi
- Seuls les participants peuvent soumettre des résultats
- Seul un admin peut modifier les wallets manuellement

---

## 🔐 Workflows d'Authentification

### Workflow 1 : Connexion via OAuth (Google/Apple/Facebook)

1. **Utilisateur clique sur "Connexion avec Google"**
2. Redirection vers Google OAuth
3. Google retourne : `provider_user_id`, `email`, `name`, `avatar_url`, `access_token`
4. Système vérifie si `oauth_providers.provider_user_id` existe
   - **Si existe** → Récupérer le `user_id` → Connexion
   - **Si n'existe pas** → Vérifier si `users.email` existe
     - **Si email existe** → Lier ce compte OAuth au user existant
     - **Si email n'existe pas** → Créer nouveau user + oauth_provider
5. Email automatiquement vérifié (`email_verified_at = now()`)
6. Générer token Sanctum → Retourner au frontend

### Workflow 2 : Connexion via Magic Link (Email)

1. **Utilisateur entre son email**
2. Système vérifie si `users.email` existe
   - **Si n'existe pas** → Créer nouveau user avec cet email
   - **Si existe** → Continuer
3. Générer token unique (64 caractères aléatoires)
4. Créer entrée dans `login_tokens` :
   - `email`, `token`, `expires_at = now() + 15min`, `ip_address`, `user_agent`
5. Envoyer email avec lien : `https://mlm.app/auth/verify?token=XXXXXX`
6. **Utilisateur clique sur le lien**
7. Frontend extrait le token et appelle l'API : `POST /api/auth/verify-token`
8. Backend vérifie :
   - Token existe
   - Token non expiré (`expires_at > now()`)
   - Token non utilisé (`is_used = false`)
9. Si valide :
   - Marquer token comme utilisé (`is_used = true`, `used_at = now()`)
   - Marquer email comme vérifié (`email_verified_at = now()`)
   - Générer token Sanctum → Retourner au frontend
10. Connexion réussie

### Workflow 3 : Inscription Complète (Nouvelle utilisateur)

1. Utilisateur se connecte (OAuth ou Magic Link)
2. User créé avec `role = 'player'`
3. **Redirection vers "Compléter le profil"**
4. Utilisateur remplit :
   - WhatsApp, Pays, Ville
   - Sélectionne les jeux pratiqués
   - Pour chaque jeu : Pseudo + Upload screenshot
5. Création du `Profile` avec `status = 'pending'`
6. Création des `GameAccount` pour chaque jeu
7. **Profil en attente de validation**
8. Modérateur valide le profil
9. Système :
   - Met à jour `profiles.status = 'validated'`
   - Crée le `Wallet` avec `balance = 10.00`
   - Crée une `Transaction` de type `initial_bonus`
10. Utilisateur peut maintenant s'inscrire aux tournois

---

## 🚀 Migrations Laravel

Ordre de création des migrations :

1. `users`
2. `oauth_providers`
3. `login_tokens`
4. `profiles`
5. `game_accounts`
6. `wallets`
7. `tournaments`
8. `tournament_registrations`
9. `transactions`
10. `rounds`
11. `matches`
12. `match_results`

Commandes :

```bash
php artisan make:migration create_users_table
php artisan make:migration create_oauth_providers_table
php artisan make:migration create_login_tokens_table
php artisan make:migration create_profiles_table
php artisan make:migration create_game_accounts_table
php artisan make:migration create_wallets_table
php artisan make:migration create_tournaments_table
php artisan make:migration create_tournament_registrations_table
php artisan make:migration create_transactions_table
php artisan make:migration create_rounds_table
php artisan make:migration create_matches_table
php artisan make:migration create_match_results_table
```

### Packages Laravel Recommandés

**Pour OAuth Social (Laravel Socialite)** :

```bash
composer require laravel/socialite
```

Configuration dans `config/services.php` :

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI'),
],

'apple' => [
    'client_id' => env('APPLE_CLIENT_ID'),
    'client_secret' => env('APPLE_CLIENT_SECRET'),
    'redirect' => env('APPLE_REDIRECT_URI'),
],
```

**Pour les Magic Links** :

Package suggéré : `grosv/laravel-passwordless-login`

```bash
composer require grosv/laravel-passwordless-login
```

Ou implémentation custom via Laravel Mail + Queue.

---

**Fin du Document**

Ce schéma est optimisé pour le MVP et peut être étendu en Phase 2 pour les fonctionnalités avancées (ELO Rank, Divisions, Chat, etc.).
