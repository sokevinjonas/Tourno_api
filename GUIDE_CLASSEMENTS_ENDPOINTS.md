# Documentation API - Classements et Statistiques

## 🎯 Vue d'ensemble

Ce document décrit tous les **endpoints de classements et statistiques** disponibles.

**État:** ✅ **IMPLÉMENTÉ** - Tous les endpoints sont fonctionnels et prêts à utiliser

---

## 📊 Système de classement

### Principe

Le système utilise un **rating par points** (similaire à ELO):
- Chaque joueur commence à **1000 points**
- Les points gagnés dépendent de:
  - **Classement final** (1er = 100 pts, 2e = 75 pts, etc.)
  - **Taille du tournoi** (64 joueurs = x2, 16 joueurs = x1.5)
  - **Entry fee** (tournoi payant = bonus jusqu'à x1.5)

### Types de classements

1. **Classement global** - Tous jeux confondus
2. **Classement par jeu** - eFootball, FC25, Rocket League, Warzone
3. **Classement par tournoi** - Résultats d'un tournoi spécifique

---

## 🔌 Endpoints disponibles

### 1. Classement global

```http
GET /api/leaderboard/global
```

**Authentification:** ❌ Non requise (Public)

**Paramètres:**
- `page` (int, optionnel) - Numéro de page (défaut: 1)
- `per_page` (int, optionnel) - Résultats par page (défaut: 25, max: 100)

**Exemple:**

```bash
GET /api/leaderboard/global?page=1&per_page=25
```

**Réponse (200):**

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
        "win_rate": 67.5,
        "total_matches_played": 156,
        "total_matches_won": 105,
        "total_prize_money": 1250.00
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 25,
    "total": 247
  }
}
```

**Utilisation frontend:**

```javascript
const fetchGlobalLeaderboard = async (page = 1) => {
  const response = await fetch(`/api/leaderboard/global?page=${page}&per_page=25`);
  const data = await response.json();
  return data;
};
```

---

### 2. Classement par jeu

```http
GET /api/leaderboard/by-game/{game}
```

**Authentification:** ❌ Non requise (Public)

**Paramètres d'URL:**
- `game` (string, requis) - Jeu: `efootball`, `fc25`, `rocket_league`, `warzone`

**Paramètres de requête:**
- `page` (int, optionnel) - Numéro de page (défaut: 1)
- `per_page` (int, optionnel) - Résultats par page (défaut: 25, max: 100)

**Exemple:**

```bash
GET /api/leaderboard/by-game/efootball?page=1&per_page=25
```

**Réponse (200):**

```json
{
  "game": "efootball",
  "leaderboard": [
    {
      "rank": 1,
      "user": {
        "id": 5,
        "name": "ProGamer",
        "avatar_url": "https://..."
      },
      "stats": {
        "rating_points": 1850,
        "tournaments_played": 15,
        "tournaments_won": 5,
        "win_rate": 68.3,
        "total_matches_played": 61,
        "total_matches_won": 42,
        "total_prize_money": 750.00
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 25,
    "total": 120
  }
}
```

**Erreur - Jeu invalide (400):**

```json
{
  "message": "Invalid game",
  "valid_games": ["efootball", "fc25", "rocket_league", "warzone"]
}
```

**Utilisation frontend:**

```javascript
const fetchGameLeaderboard = async (game, page = 1) => {
  const response = await fetch(`/api/leaderboard/by-game/${game}?page=${page}`);

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }

  return await response.json();
};
```

---

### 3. Statistiques d'un joueur

```http
GET /api/users/{id}/stats
```

**Authentification:** ❌ Non requise (Public)

**Paramètres d'URL:**
- `id` (int, requis) - ID de l'utilisateur

**Exemple:**

```bash
GET /api/users/5/stats
```

**Réponse (200):**

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
    "total_matches_played": 156,
    "total_matches_won": 105,
    "total_matches_lost": 48,
    "total_matches_draw": 3,
    "win_rate": 67.3,
    "total_prize_money": 1250.00
  },
  "stats_by_game": {
    "efootball": {
      "rating_points": 1850,
      "rank": 1,
      "tournaments_played": 15,
      "tournaments_won": 5,
      "total_matches_played": 61,
      "total_matches_won": 42,
      "win_rate": 68.9,
      "total_prize_money": 750.00
    },
    "fc25": {
      "rating_points": 1620,
      "rank": 3,
      "tournaments_played": 9,
      "tournaments_won": 3,
      "total_matches_played": 45,
      "total_matches_won": 30,
      "win_rate": 66.7,
      "total_prize_money": 500.00
    }
  },
  "recent_tournaments": [
    {
      "id": 10,
      "name": "FIFA Championship",
      "game": "efootball",
      "final_rank": 1,
      "prize_won": 50.00,
      "completed_at": "2025-12-25T14:30:00.000000Z"
    }
  ]
}
```

**Erreur - Utilisateur non trouvé (404):**

```json
{
  "message": "User not found"
}
```

**Utilisation frontend:**

```javascript
const fetchUserStats = async (userId) => {
  const response = await fetch(`/api/users/${userId}/stats`);

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error('Utilisateur non trouvé');
    }
    throw new Error('Erreur lors de la récupération des stats');
  }

  return await response.json();
};
```

---

### 4. Classement d'un tournoi

```http
GET /api/tournaments/{id}/rankings
```

**Authentification:** ❌ Non requise (Public)

**Paramètres d'URL:**
- `id` (int, requis) - ID du tournoi

**Exemple:**

```bash
GET /api/tournaments/10/rankings
```

**Réponse (200):**

```json
{
  "tournament": {
    "id": 10,
    "name": "FIFA Championship",
    "game": "efootball",
    "format": "single_elimination",
    "status": "completed",
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
        "eliminated_round": null,
        "prize_won": 50.00
      }
    },
    {
      "rank": 2,
      "user": {
        "id": 8,
        "name": "Runner-up",
        "avatar_url": "https://..."
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

**Erreur - Tournoi non trouvé (404):**

```json
{
  "message": "Tournament not found"
}
```

**Utilisation frontend:**

```javascript
const fetchTournamentRankings = async (tournamentId) => {
  const response = await fetch(`/api/tournaments/${tournamentId}/rankings`);

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error('Tournoi non trouvé');
    }
    throw new Error('Erreur lors de la récupération du classement');
  }

  return await response.json();
};
```

---

## 💡 Exemples d'implémentation frontend

### 1. Page Leaderboard avec onglets

```jsx
import { useState, useEffect } from 'react';

const LeaderboardPage = () => {
  const [activeTab, setActiveTab] = useState('global');
  const [leaderboard, setLeaderboard] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchLeaderboard(activeTab, 1);
  }, [activeTab]);

  const fetchLeaderboard = async (game, page) => {
    setLoading(true);
    try {
      const url = game === 'global'
        ? `/api/leaderboard/global?page=${page}`
        : `/api/leaderboard/by-game/${game}?page=${page}`;

      const response = await fetch(url);
      const data = await response.json();

      setLeaderboard(data.leaderboard);
      setPagination(data.pagination);
    } catch (error) {
      console.error('Erreur:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="leaderboard-page">
      <h1>Classements</h1>

      {/* Onglets */}
      <Tabs>
        <Tab
          active={activeTab === 'global'}
          onClick={() => setActiveTab('global')}
        >
          🌍 Global
        </Tab>
        <Tab
          active={activeTab === 'efootball'}
          onClick={() => setActiveTab('efootball')}
        >
          ⚽ eFootball
        </Tab>
        <Tab
          active={activeTab === 'fc25'}
          onClick={() => setActiveTab('fc25')}
        >
          🎮 FC25
        </Tab>
      </Tabs>

      {/* Tableau */}
      {loading ? (
        <Loader />
      ) : (
        <>
          <LeaderboardTable data={leaderboard} />
          <Pagination
            current={pagination?.current_page}
            total={pagination?.last_page}
            onPageChange={(page) => fetchLeaderboard(activeTab, page)}
          />
        </>
      )}
    </div>
  );
};
```

### 2. Composant Tableau de classement

```jsx
const LeaderboardTable = ({ data }) => {
  return (
    <table className="leaderboard-table">
      <thead>
        <tr>
          <th>Rang</th>
          <th>Joueur</th>
          <th>Rating</th>
          <th>Tournois</th>
          <th>Win Rate</th>
          <th>Prize Money</th>
        </tr>
      </thead>
      <tbody>
        {data.map((player) => (
          <tr key={player.user.id}>
            <td>
              <RankBadge rank={player.rank} />
            </td>
            <td>
              <PlayerInfo
                name={player.user.name}
                avatar={player.user.avatar_url}
                userId={player.user.id}
              />
            </td>
            <td>
              <strong>{player.stats.global_rating || player.stats.rating_points}</strong>
            </td>
            <td>
              {player.stats.tournaments_won}/{player.stats.tournaments_played}
            </td>
            <td>
              <WinRateBadge rate={player.stats.win_rate} />
            </td>
            <td>
              {player.stats.total_prize_money} MLM
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
};
```

### 3. Page Profil Joueur

```jsx
const PlayerProfile = ({ userId }) => {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchUserStats(userId);
  }, [userId]);

  const fetchUserStats = async (id) => {
    try {
      const response = await fetch(`/api/users/${id}/stats`);
      const data = await response.json();
      setStats(data);
    } catch (error) {
      console.error('Erreur:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <Loader />;
  if (!stats) return <NotFound />;

  return (
    <div className="player-profile">
      {/* Header */}
      <ProfileHeader user={stats.user}>
        <RatingBadge
          rating={stats.global_stats?.global_rating || 1000}
          rank={stats.global_stats?.global_rank || '—'}
        />
      </ProfileHeader>

      {/* Stats globales */}
      <StatsGrid>
        <StatCard
          label="Tournois joués"
          value={stats.global_stats?.tournaments_played || 0}
          icon="🎮"
        />
        <StatCard
          label="Victoires"
          value={stats.global_stats?.tournaments_won || 0}
          icon="🏆"
        />
        <StatCard
          label="Win Rate"
          value={`${stats.global_stats?.win_rate || 0}%`}
          icon="📊"
        />
        <StatCard
          label="Prize Money"
          value={`${stats.global_stats?.total_prize_money || 0} MLM`}
          icon="💰"
        />
      </StatsGrid>

      {/* Stats par jeu */}
      <GameStatsSection>
        <h3>Statistiques par jeu</h3>
        {Object.entries(stats.stats_by_game || {}).map(([game, gameStats]) => (
          <GameStatCard key={game} game={game} stats={gameStats} />
        ))}
      </GameStatsSection>

      {/* Tournois récents */}
      <RecentTournamentsSection>
        <h3>Tournois récents</h3>
        <TournamentList tournaments={stats.recent_tournaments || []} />
      </RecentTournamentsSection>
    </div>
  );
};
```

### 4. Page Tournoi - Classement final

```jsx
const TournamentRankings = ({ tournamentId }) => {
  const [data, setData] = useState(null);

  useEffect(() => {
    fetch(`/api/tournaments/${tournamentId}/rankings`)
      .then(res => res.json())
      .then(setData);
  }, [tournamentId]);

  if (!data) return <Loader />;

  const champion = data.rankings[0];

  return (
    <div className="tournament-rankings">
      <h2>Classement Final</h2>

      {/* Champion mis en avant */}
      {champion && (
        <ChampionBanner>
          <Trophy size="large" />
          <Avatar user={champion.user} size="xl" />
          <h3>{champion.user.name}</h3>
          <p>
            {champion.stats.wins} victoires - {champion.stats.prize_won} MLM
          </p>
        </ChampionBanner>
      )}

      {/* Reste du classement */}
      <RankingsTable>
        {data.rankings.slice(1).map((reg) => (
          <RankingRow
            key={reg.user.id}
            rank={reg.rank}
            user={reg.user}
            stats={reg.stats}
          />
        ))}
      </RankingsTable>
    </div>
  );
};
```

---

## 🎨 Composants UI recommandés

### Badge de rang

```jsx
const RankBadge = ({ rank }) => {
  if (rank === 1) return <span className="rank gold">🥇 #{rank}</span>;
  if (rank === 2) return <span className="rank silver">🥈 #{rank}</span>;
  if (rank === 3) return <span className="rank bronze">🥉 #{rank}</span>;
  return <span className="rank">#{rank}</span>;
};
```

### Badge de rating

```jsx
const RatingBadge = ({ rating, rank }) => {
  const tier = rating >= 2000 ? 'legend' :
               rating >= 1500 ? 'master' :
               rating >= 1200 ? 'expert' : 'rookie';

  return (
    <div className={`rating-badge ${tier}`}>
      <div className="rating">{rating}</div>
      <div className="rank">Rang #{rank}</div>
    </div>
  );
};
```

### Badge de win rate

```jsx
const WinRateBadge = ({ rate }) => {
  const color = rate >= 70 ? 'green' :
                rate >= 50 ? 'yellow' : 'red';

  return (
    <span className={`win-rate ${color}`}>
      {rate.toFixed(1)}%
    </span>
  );
};
```

---

## 📊 Données disponibles

### Global Stats (classement global)

| Champ | Type | Description |
|-------|------|-------------|
| `global_rating` | int | Points de rating global |
| `global_rank` | int | Rang global |
| `tournaments_played` | int | Tournois joués |
| `tournaments_won` | int | Tournois gagnés |
| `total_matches_played` | int | Matchs joués |
| `total_matches_won` | int | Matchs gagnés |
| `total_matches_lost` | int | Matchs perdus |
| `total_matches_draw` | int | Matchs nuls |
| `win_rate` | float | Pourcentage de victoire (0-100) |
| `total_prize_money` | float | Argent total gagné |

### Game Stats (classement par jeu)

| Champ | Type | Description |
|-------|------|-------------|
| `rating_points` | int | Points de rating pour ce jeu |
| `rank` | int | Rang pour ce jeu |
| `tournaments_played` | int | Tournois joués |
| `tournaments_won` | int | Tournois gagnés |
| `total_matches_played` | int | Matchs joués |
| `total_matches_won` | int | Matchs gagnés |
| `win_rate` | float | Pourcentage de victoire (0-100) |
| `total_prize_money` | float | Argent gagné dans ce jeu |

---

## ⚙️ Mise à jour automatique

Les statistiques sont **automatiquement mises à jour** lors de la complétion d'un tournoi via:

```http
POST /api/tournaments/{id}/complete
```

**Ce qui est calculé automatiquement:**
- ✅ Points de rating gagnés selon le classement
- ✅ Statistiques de matchs (joués, gagnés, perdus, nuls)
- ✅ Prize money total
- ✅ Tournois joués et gagnés

**Formule de calcul des points:**

```
Points = Base × Multiplicateur Participants × Multiplicateur Entry Fee

Base selon rang:
- 1er: 100 pts
- 2e: 75 pts
- 3e: 50 pts
- 4e: 40 pts
- 5e+: Dégressif

Multiplicateur Participants:
- 64+ joueurs: x2.0
- 32-63: x1.75
- 16-31: x1.5
- 8-15: x1.25
- Moins: x1.0

Multiplicateur Entry Fee:
- 50+ pièces: x1.5
- 20-49: x1.3
- 10-19: x1.2
- 1-9: x1.1
- Gratuit: x1.0
```

**Exemple:**
- Tournoi: 16 joueurs, entry fee 25 pièces
- Classement: 1er place
- Calcul: `100 × 1.5 × 1.3 = 195 points`

---

## 🔒 Permissions

Tous les endpoints de classements sont **publics** (pas d'authentification requise).

Seul l'endpoint de complétion de tournoi nécessite d'être l'organisateur ou admin:

```http
POST /api/tournaments/{id}/complete  // Requiert: Organizer OR Admin
```

---

## 📝 Notes importantes

### 1. Pagination

Tous les endpoints de leaderboard supportent la pagination:
- Par défaut: 25 résultats par page
- Maximum: 100 résultats par page
- Format standard Laravel avec `current_page`, `last_page`, `per_page`, `total`

### 2. Calcul du rang

Le rang est calculé **en temps réel** à chaque requête pour garantir la précision.

### 3. Joueurs sans stats

Si un joueur n'a participé à aucun tournoi:
- `global_stats` sera `null`
- `stats_by_game` sera un objet vide `{}`
- `recent_tournaments` sera un tableau vide `[]`

### 4. Jeux supportés

```javascript
const VALID_GAMES = ['efootball', 'fc25', 'rocket_league', 'warzone'];
```

---

## 🚀 Checklist d'intégration frontend

### Étape 1: Pages à créer
- [ ] Page `/leaderboard` - Classement global avec onglets par jeu
- [ ] Page `/players/:id` - Profil joueur avec stats
- [ ] Section classement dans page tournoi

### Étape 2: Composants à créer
- [ ] `LeaderboardTable` - Tableau de classement
- [ ] `PlayerStatsCard` - Carte de stats joueur
- [ ] `RankBadge` - Badge de rang avec médailles
- [ ] `RatingBadge` - Badge de rating avec tier
- [ ] `TournamentRankings` - Classement tournoi

### Étape 3: Services API
- [ ] `leaderboardService.js` - Fonctions pour tous les endpoints
- [ ] Gestion du cache (optionnel mais recommandé)
- [ ] Gestion des erreurs

### Étape 4: UI/UX
- [ ] Design des tiers de rating (Legend, Master, Expert, Rookie)
- [ ] Animations pour les classements
- [ ] Mise en avant du top 3
- [ ] Highlight du joueur connecté dans le classement

---

## 📞 Support

Pour toute question sur l'intégration:
1. Consulter ce document
2. Tester les endpoints en environnement de développement
3. Vérifier les exemples de code fournis

---

**Dernière mise à jour:** 2025-12-25
**Version API:** 1.0
**Statut:** ✅ Production Ready
