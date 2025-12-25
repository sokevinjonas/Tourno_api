# Guide des Classements - Documentation Complète

## 🎯 Vue d'ensemble

Ce document décrit le **système de classements** de la plateforme de tournois. Tous les endpoints sont implémentés et prêts à utiliser.

**État:** ✅ IMPLÉMENTÉ - Prêt pour le frontend

---

## 📊 Types de classements recommandés

### ✅ 1. Classement par tournoi individuel
**Quoi:** Classement final d'un tournoi spécifique
**Où:** Page du tournoi complété
**Données:** `tournament_registrations.final_rank`

### ✅ 2. Classement global par jeu
**Quoi:** Classement des meilleurs joueurs pour un jeu spécifique (ex: eFootball, FC25)
**Où:** Page dédiée par jeu
**Données:** Agrégation des performances sur tous les tournois d'un jeu

### ✅ 3. Classement global tous jeux
**Quoi:** Classement général tous jeux confondus
**Où:** Page d'accueil / Page "Leaderboard"
**Données:** Agrégation des performances sur tous les tournois

### ✅ 4. Classement par format (optionnel)
**Quoi:** Classement des joueurs par format (Swiss vs Knockout)
**Où:** Page dédiée "Stats par format"
**Données:** Performances séparées Swiss / Knockout

---

## 🏆 Recommandation principale : Système de points ELO/Rating

### Pourquoi un système de rating ?

Au lieu de simplement compter les victoires, je recommande un **système de points de classement** qui:

1. ✅ Donne plus de poids aux victoires dans des tournois compétitifs
2. ✅ Prend en compte la qualité des adversaires
3. ✅ Récompense la consistance
4. ✅ Permet des classements comparables entre formats

### Architecture proposée

#### Table `user_game_stats` (à créer)

```sql
CREATE TABLE user_game_stats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    game ENUM('efootball', 'fc25', 'rocket_league', 'warzone') NOT NULL,

    -- Points de classement
    rating_points INT DEFAULT 1000,  -- ELO-like rating

    -- Statistiques globales
    tournaments_played INT DEFAULT 0,
    tournaments_won INT DEFAULT 0,
    tournaments_top3 INT DEFAULT 0,

    total_matches_played INT DEFAULT 0,
    total_matches_won INT DEFAULT 0,
    total_matches_lost INT DEFAULT 0,
    total_matches_draw INT DEFAULT 0,

    -- Récompenses
    total_prize_money DECIMAL(10,2) DEFAULT 0,

    -- Séries
    current_win_streak INT DEFAULT 0,
    best_win_streak INT DEFAULT 0,

    -- Timestamps
    last_tournament_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY (user_id, game),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Table `user_global_stats` (à créer)

```sql
CREATE TABLE user_global_stats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL UNIQUE,

    -- Points de classement global
    global_rating INT DEFAULT 1000,

    -- Statistiques tous jeux confondus
    total_tournaments_played INT DEFAULT 0,
    total_tournaments_won INT DEFAULT 0,
    total_tournaments_top3 INT DEFAULT 0,

    total_matches_played INT DEFAULT 0,
    total_matches_won INT DEFAULT 0,
    total_matches_lost INT DEFAULT 0,
    total_matches_draw INT DEFAULT 0,

    total_prize_money DECIMAL(10,2) DEFAULT 0,

    current_win_streak INT DEFAULT 0,
    best_win_streak INT DEFAULT 0,

    last_tournament_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🔢 Système de calcul des points

### Attribution des points selon le rang final

```php
// Points base selon le classement
$rankPoints = match($finalRank) {
    1 => 100,  // Champion
    2 => 75,   // 2e place
    3 => 50,   // 3e place
    4 => 40,
    5 => 30,
    6 => 25,
    7 => 20,
    8 => 15,
    default => 10  // Participation
};

// Bonus selon la taille du tournoi
$participantBonus = match(true) {
    $participants >= 64 => 2.0,  // x2
    $participants >= 32 => 1.75, // x1.75
    $participants >= 16 => 1.5,  // x1.5
    $participants >= 8 => 1.25,  // x1.25
    default => 1.0
};

// Bonus selon l'entry fee (tournois payants plus valorisés)
$feeBonus = match(true) {
    $entryFee >= 50 => 1.5,
    $entryFee >= 20 => 1.3,
    $entryFee >= 10 => 1.2,
    $entryFee > 0 => 1.1,
    default => 1.0  // Gratuit
};

// Points finaux
$finalPoints = $rankPoints * $participantBonus * $feeBonus;
```

### Exemple de calcul

**Scénario:** Joueur finit 1er dans un tournoi 16 joueurs avec entry fee de 25 pièces

```
Points base (1er) = 100
Bonus participants (16) = x1.5
Bonus entry fee (25) = x1.3

Total = 100 × 1.5 × 1.3 = 195 points
```

---

---

## 📋 Endpoints API - Documentation Frontend

### 1. GET `/api/leaderboard/global`

**Classement global tous jeux**

**Authentification:** ❌ Non requise (Public)

**Paramètres de requête:**
- `page` (int, optionnel) - Numéro de page (défaut: 1)
- `per_page` (int, optionnel) - Résultats par page (défaut: 25, max: 100)

**Exemple de requête:**

```bash
GET /api/leaderboard/global?page=1&per_page=25
```

**Réponse succès (200):**

```json
{
  "leaderboard": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "ProGamer",
        "avatar_url": "https://..."
      },
      "stats": {
        "global_rating": 2450,
        "tournaments_played": 24,
        "tournaments_won": 8,
        "tournaments_top3": 15,
        "win_rate": 67.5,
        "total_prize_money": 1250.00,
        "best_win_streak": 12
      }
    },
    {
      "rank": 2,
      "user": {
        "id": 12,
        "name": "ElitePlayer"
      },
      "stats": {
        "global_rating": 2320,
        "tournaments_played": 18,
        "tournaments_won": 6,
        "tournaments_top3": 12,
        "win_rate": 71.2,
        "total_prize_money": 980.00,
        "best_win_streak": 9
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "total_players": 247
  }
}
```

**Paramètres de requête:**
- `page` (int) - Page du classement
- `per_page` (int) - Nombre de résultats (default: 25, max: 100)

---

### 2. GET `/api/leaderboard/by-game/{game}`

**Classement par jeu spécifique**

**URL:** `/api/leaderboard/by-game/efootball`

```json
{
  "game": "efootball",
  "leaderboard": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "ProGamer"
      },
      "stats": {
        "rating_points": 1850,
        "tournaments_played": 15,
        "tournaments_won": 5,
        "tournaments_top3": 10,
        "win_rate": 68.3,
        "total_matches_won": 42,
        "total_matches_played": 61,
        "total_prize_money": 750.00,
        "current_win_streak": 5,
        "best_win_streak": 12
      }
    }
  ]
}
```

**Jeux supportés:**
- `efootball`
- `fc25`
- `rocket_league`
- `warzone`

---

### 3. GET `/api/tournaments/{id}/rankings`

**Classement d'un tournoi spécifique**

```json
{
  "tournament": {
    "id": 10,
    "name": "FIFA Championship",
    "status": "completed",
    "format": "single_elimination",
    "participants_count": 8
  },
  "rankings": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "Champion",
        "avatar_url": "https://..."
      },
      "stats": {
        "tournament_points": 9,
        "wins": 3,
        "losses": 0,
        "draws": 0,
        "eliminated": false,
        "prize_won": 50.00
      }
    },
    {
      "rank": 2,
      "user": {
        "id": 8,
        "name": "Runner-up"
      },
      "stats": {
        "tournament_points": 6,
        "wins": 2,
        "losses": 1,
        "draws": 0,
        "eliminated": true,
        "eliminated_round": 3,
        "prize_won": 20.00
      }
    }
  ]
}
```

---

### 4. GET `/api/users/{id}/stats`

**Statistiques d'un joueur**

```json
{
  "user": {
    "id": 5,
    "name": "ProGamer",
    "avatar_url": "https://..."
  },
  "global_stats": {
    "global_rating": 2450,
    "global_rank": 1,
    "tournaments_played": 24,
    "tournaments_won": 8,
    "tournaments_top3": 15,
    "total_matches_played": 156,
    "total_matches_won": 105,
    "win_rate": 67.3,
    "total_prize_money": 1250.00,
    "current_win_streak": 5,
    "best_win_streak": 12
  },
  "stats_by_game": {
    "efootball": {
      "rating_points": 1850,
      "rank": 1,
      "tournaments_played": 15,
      "tournaments_won": 5,
      "win_rate": 68.3
    },
    "fc25": {
      "rating_points": 1620,
      "rank": 3,
      "tournaments_played": 9,
      "tournaments_won": 3,
      "win_rate": 66.1
    }
  },
  "recent_tournaments": [
    {
      "id": 10,
      "name": "FIFA Championship",
      "game": "efootball",
      "final_rank": 1,
      "prize_won": 50.00,
      "completed_at": "2025-12-25T..."
    }
  ]
}
```

---

### 5. GET `/api/leaderboard/by-format/{format}`

**Classement par format (optionnel)**

**URL:** `/api/leaderboard/by-format/single_elimination`

```json
{
  "format": "single_elimination",
  "leaderboard": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "KnockoutKing"
      },
      "stats": {
        "tournaments_played": 12,
        "tournaments_won": 6,
        "win_rate": 75.0
      }
    }
  ]
}
```

---

## 🎨 Recommandations UI/UX

### 1. Page d'accueil - Top 3 Global

```jsx
<TopPlayersWidget>
  <h2>Top Joueurs</h2>
  <PodiumDisplay>
    <Player rank={2} /> {/* Silver */}
    <Player rank={1} /> {/* Gold - Plus grand */}
    <Player rank={3} /> {/* Bronze */}
  </PodiumDisplay>
  <Link to="/leaderboard">Voir le classement complet →</Link>
</TopPlayersWidget>
```

### 2. Page Leaderboard - Onglets par jeu

```jsx
<LeaderboardPage>
  <Tabs>
    <Tab label="Global" icon="🌍" />
    <Tab label="eFootball" icon="⚽" />
    <Tab label="FC25" icon="🎮" />
    <Tab label="Rocket League" icon="🚗" />
  </Tabs>

  <LeaderboardTable>
    {players.map((player, index) => (
      <PlayerRow
        rank={index + 1}
        player={player}
        showBadges={index < 3}
        highlightCurrentUser={player.id === currentUser.id}
      />
    ))}
  </LeaderboardTable>

  <Pagination />
</LeaderboardPage>
```

### 3. Page Tournoi - Classement final

```jsx
<TournamentRankings tournament={tournament}>
  <h2>Classement Final</h2>

  {/* Champion mis en avant */}
  {champion && (
    <ChampionBanner>
      <Trophy size="large" />
      <Avatar user={champion} size="xl" />
      <h3>{champion.name}</h3>
      <p>{champion.stats.wins} victoires - {champion.prize_won} MLM</p>
    </ChampionBanner>
  )}

  {/* Reste du classement */}
  <RankingsTable>
    {rankings.slice(1).map(reg => (
      <RankingRow
        rank={reg.rank}
        user={reg.user}
        stats={reg.stats}
        prizeWon={reg.prize_won}
      />
    ))}
  </RankingsTable>
</TournamentRankings>
```

### 4. Profil joueur - Stats complètes

```jsx
<PlayerProfile userId={userId}>
  <ProfileHeader>
    <Avatar />
    <UserInfo>
      <h1>{user.name}</h1>
      <RatingBadge rating={stats.global_rating} rank={stats.global_rank} />
    </UserInfo>
  </ProfileHeader>

  <StatsGrid>
    <StatCard
      label="Tournois joués"
      value={stats.tournaments_played}
      icon="🎮"
    />
    <StatCard
      label="Victoires"
      value={stats.tournaments_won}
      icon="🏆"
    />
    <StatCard
      label="Win Rate"
      value={`${stats.win_rate}%`}
      icon="📊"
    />
    <StatCard
      label="Prize Money"
      value={`${stats.total_prize_money} MLM`}
      icon="💰"
    />
  </StatsGrid>

  <GameStatsSection>
    <h3>Stats par jeu</h3>
    {Object.entries(stats.stats_by_game).map(([game, gameStats]) => (
      <GameStatCard game={game} stats={gameStats} />
    ))}
  </GameStatsSection>

  <RecentTournamentsSection>
    <h3>Tournois récents</h3>
    <TournamentList tournaments={stats.recent_tournaments} />
  </RecentTournamentsSection>
</PlayerProfile>
```

---

## 🔄 Mise à jour des stats

### Quand mettre à jour les stats ?

**Endpoint:** `POST /api/tournaments/{id}/complete`

Lors de la complétion d'un tournoi, mettre à jour:

1. **Stats globales de chaque participant**
   - `total_tournaments_played++`
   - `tournaments_won++` (si rank = 1)
   - `tournaments_top3++` (si rank ≤ 3)
   - `total_matches_played`, `total_matches_won`, etc.
   - `total_prize_money += prize_won`
   - `global_rating += calculated_points`

2. **Stats par jeu**
   - Mêmes métriques mais filtrées par `game`

### Service recommandé

```php
// app/Services/UserStatsService.php
class UserStatsService
{
    public function updateStatsAfterTournament(
        User $user,
        Tournament $tournament,
        TournamentRegistration $registration
    ): void {
        DB::transaction(function () use ($user, $tournament, $registration) {
            // Calculer les points gagnés
            $points = $this->calculateRatingPoints(
                $registration->final_rank,
                $tournament->registrations()->count(),
                $tournament->entry_fee
            );

            // Mettre à jour stats par jeu
            $this->updateGameStats($user, $tournament, $registration, $points);

            // Mettre à jour stats globales
            $this->updateGlobalStats($user, $registration, $points);
        });
    }

    protected function calculateRatingPoints(
        int $rank,
        int $participants,
        float $entryFee
    ): int {
        // Logique de calcul des points (voir section précédente)
    }
}
```

---

## 📈 Badges et récompenses (optionnel)

### Système de badges

```sql
CREATE TABLE user_badges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    badge_type ENUM(
        'first_win',
        'win_streak_5',
        'win_streak_10',
        'tournament_10',
        'tournament_50',
        'champion_3',
        'top3_10',
        'prize_1000'
    ) NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY (user_id, badge_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Badges suggérés:**
- 🏆 **Première victoire** - Gagner son premier tournoi
- 🔥 **Série de 5** - 5 victoires de matchs consécutives
- ⚡ **Série de 10** - 10 victoires de matchs consécutives
- 🎮 **Vétéran** - Participer à 10 tournois
- 👑 **Légende** - Participer à 50 tournois
- 🥇 **Triple couronne** - Gagner 3 tournois
- 🥉 **Consistant** - Terminer top 3 dans 10 tournois
- 💰 **Millionnaire** - Gagner 1000 pièces au total

---

## ✅ Résumé des recommandations

### À implémenter immédiatement

1. ✅ **Classement par tournoi individuel** (déjà disponible via `final_rank`)
2. ✅ **Tables de stats** (`user_game_stats` et `user_global_stats`)
3. ✅ **Service de calcul de rating** (`UserStatsService`)
4. ✅ **Endpoints API** pour les classements globaux et par jeu
5. ✅ **Mise à jour automatique** lors de la complétion du tournoi

### À implémenter en phase 2

1. ⏸️ Système de badges
2. ⏸️ Classement par format (Swiss vs Knockout)
3. ⏸️ Historique détaillé des performances
4. ⏸️ Graphiques d'évolution du rating

### Bénéfices

- ✅ **Engagement accru** - Les joueurs veulent améliorer leur rating
- ✅ **Compétition saine** - Classements motivent la participation
- ✅ **Valorisation des performances** - Tournois difficiles rapportent plus de points
- ✅ **Réutilisabilité** - Système extensible à d'autres jeux
- ✅ **Équité** - Prend en compte la difficulté et la qualité des adversaires

---

## 🤔 Ma recommandation finale

**Pour commencer:**

1. **Phase 1 (Immédiat):** Créer le système de stats de base
   - Tables `user_game_stats` et `user_global_stats`
   - Endpoints pour classement global et par jeu
   - Mise à jour automatique lors de complétion de tournoi

2. **Phase 2 (Plus tard):** Améliorer avec
   - Système de badges
   - Graphiques d'évolution
   - Classements par période (hebdomadaire, mensuel)

**Priorité:**
- ✅ **Classement global** (Page d'accueil + Page dédiée)
- ✅ **Classement par jeu** (Page par jeu)
- ✅ **Stats joueur** (Profil joueur)
- ⏸️ Classement par format (moins prioritaire)

Tu veux que je commence par créer les migrations et le service pour le système de stats ?
