# Guide de Migration Frontend - Passage aux UUIDs

## Date: 2026-01-01
## Version: 2.0.0

---

## Résumé des changements

L'API a migré d'un système d'identification basé sur des IDs numériques (1, 2, 3...) vers des UUIDs (universally unique identifiers). Cette migration améliore la sécurité et permet une meilleure scalabilité.

### Entités concernées
- Tournaments (Tournois)
- Tournament Registrations (Inscriptions)
- Rounds (Rondes)
- Matches (Matchs)
- Match Results (Résultats de matchs)
- Match Evidence (Preuves de matchs)
- Match Messages (Messages de matchs)
- Tournament Wallet Locks (Verrouillages de portefeuille)

---

## Changements requis dans le Frontend

### 1. Format des IDs

#### ❌ AVANT
```javascript
// Les IDs étaient des nombres
const tournamentId = 1;
const url = `/api/tournaments/${tournamentId}`;
```

#### ✅ MAINTENANT
```javascript
// Les IDs sont des UUIDs (strings)
const tournamentId = "550e8400-e29b-41d4-a716-446655440000";
const url = `/api/tournaments/${tournamentId}`;
```

### 2. Réponses API - IDs masqués

#### ❌ AVANT
```json
{
  "tournament": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "organizer_id": 5,
    "name": "Tournoi eFootball",
    "status": "open"
  }
}
```

#### ✅ MAINTENANT
```json
{
  "tournament": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Tournoi eFootball",
    "status": "open",
    "organizer": {
      "uuid": "123e4567-e89b-12d3-a456-426614174000",
      "name": "G4me Pro Africa",
      "email": "organizer@example.com"
    }
  }
}
```

**Note importante:** Les champs `id`, `organizer_id`, `tournament_id`, etc. ne sont PLUS retournés par l'API.

### 3. Routes API

#### Endpoints modifiés

| Endpoint | AVANT | MAINTENANT |
|----------|-------|------------|
| Détail tournoi | `GET /api/tournaments/1` | `GET /api/tournaments/{uuid}` |
| Update tournoi | `PUT /api/tournaments/1` | `PUT /api/tournaments/{uuid}` |
| Delete tournoi | `DELETE /api/tournaments/1` | `DELETE /api/tournaments/{uuid}` |
| Changer statut | `POST /api/tournaments/1/status` | `POST /api/tournaments/{uuid}/status` |
| Matchs d'un tournoi | `GET /api/tournaments/1/matches` | `GET /api/tournaments/{uuid}/matches` |
| Info des rondes | `GET /api/tournaments/1/rounds-info` | `GET /api/tournaments/{uuid}/rounds-info` |
| Inscription | `POST /api/tournaments/1/register` | `POST /api/tournaments/{uuid}/register` |
| Désinscription | `POST /api/tournaments/1/withdraw` | `POST /api/tournaments/{uuid}/withdraw` |
| Participants | `GET /api/tournaments/1/participants` | `GET /api/tournaments/{uuid}/participants` |
| Classement | `GET /api/tournaments/1/leaderboard` | `GET /api/tournaments/{uuid}/leaderboard` |
| Vérifier inscription | `GET /api/tournaments/1/check-registration` | `GET /api/tournaments/{uuid}/check-registration` |
| Démarrer tournoi | `POST /api/rounds/start/1` | `POST /api/rounds/start/{uuid}` |
| Générer ronde | `POST /api/rounds/generate/1` | `POST /api/rounds/generate/{uuid}` |
| Compléter ronde | `POST /api/rounds/complete/1` | `POST /api/rounds/complete/{uuid}` |
| Classement tournoi | `GET /api/leaderboard/tournament/1` | `GET /api/leaderboard/tournament/{uuid}` |

### 4. Modifications requises dans le code Frontend

#### A. State Management (Vuex/Pinia/Redux)

```javascript
// ❌ AVANT
const state = {
  currentTournament: {
    id: 1,
    name: "Tournoi eFootball"
  }
}

// ✅ MAINTENANT
const state = {
  currentTournament: {
    uuid: "550e8400-e29b-41d4-a716-446655440000",
    name: "Tournoi eFootball"
  }
}
```

#### B. Composants/Components

```javascript
// ❌ AVANT - Vue.js
export default {
  props: {
    tournamentId: {
      type: Number,
      required: true
    }
  },
  methods: {
    fetchTournament() {
      axios.get(`/api/tournaments/${this.tournamentId}`)
    }
  }
}

// ✅ MAINTENANT - Vue.js
export default {
  props: {
    tournamentId: {
      type: String, // UUID est une string
      required: true
    }
  },
  methods: {
    fetchTournament() {
      axios.get(`/api/tournaments/${this.tournamentId}`)
    }
  }
}
```

```javascript
// ❌ AVANT - React
interface TournamentProps {
  tournamentId: number;
}

// ✅ MAINTENANT - React
interface TournamentProps {
  tournamentId: string; // UUID est une string
}
```

#### C. Routing

```javascript
// ❌ AVANT - Vue Router
{
  path: '/tournaments/:id',
  name: 'tournament-detail',
  component: TournamentDetail,
  props: route => ({ tournamentId: Number(route.params.id) })
}

// ✅ MAINTENANT - Vue Router
{
  path: '/tournaments/:uuid',
  name: 'tournament-detail',
  component: TournamentDetail,
  props: route => ({ tournamentId: route.params.uuid })
}
```

```javascript
// ❌ AVANT - React Router
<Route path="/tournaments/:id" element={<TournamentDetail />} />

// Usage
const { id } = useParams();
const tournamentId = parseInt(id);

// ✅ MAINTENANT - React Router
<Route path="/tournaments/:uuid" element={<TournamentDetail />} />

// Usage
const { uuid } = useParams();
const tournamentId = uuid; // Déjà une string
```

#### D. LocalStorage / SessionStorage

```javascript
// ❌ AVANT
localStorage.setItem('lastTournamentId', '1');

// ✅ MAINTENANT
localStorage.setItem('lastTournamentId', '550e8400-e29b-41d4-a716-446655440000');
```

#### E. Comparaisons

```javascript
// ❌ AVANT
if (tournament.id === 1) {
  // ...
}

// ✅ MAINTENANT
if (tournament.uuid === "550e8400-e29b-41d4-a716-446655440000") {
  // ...
}
```

#### F. Filtrage et recherche

```javascript
// ❌ AVANT
const tournament = tournaments.find(t => t.id === 1);

// ✅ MAINTENANT
const tournament = tournaments.find(t => t.uuid === "550e8400-e29b-41d4-a716-446655440000");
```

### 5. Accès aux relations

Les relations sont maintenant retournées via eager loading. Utilisez les objets imbriqués au lieu des IDs.

#### ❌ AVANT
```javascript
// Récupérer l'ID de l'organisateur
const organizerId = tournament.organizer_id;

// Faire une requête séparée
axios.get(`/api/users/${organizerId}`);
```

#### ✅ MAINTENANT
```javascript
// L'organisateur est déjà inclus dans la réponse
const organizer = tournament.organizer;
console.log(organizer.name);
console.log(organizer.email);
console.log(organizer.organizerProfile.badge); // certified, verified, etc.
```

### 6. Nouveaux champs disponibles

#### Tournois
- `organizer.organizerProfile.badge` - Badge de l'organisateur (certified, verified, standard)

#### Règles du tournoi
Les tournois ont maintenant un champ `rules` (JSON array) contenant les règles du tournoi.

```json
{
  "rules": [
    "Fair-play et respect mutuel exigés.",
    "Format Suisse : 3 rondes minimum garanties.",
    "Engagement : chaque joueur doit effectuer tous ses matchs.",
    "Responsabilité technique : chaque joueur est garant de sa propre connexion internet.",
    "Barème des points : Victoire = 3 pts, Nul = 1 pt, Défaite ou non-joué = 0 pt.",
    "Validation : capture d'écran du score final obligatoire après chaque match.",
    "Sécurité : enregistrement vidéo conseillé pour servir de preuve en cas de litige."
  ]
}
```

---

## Checklist de migration

### Phase 1: Audit du code
- [ ] Identifier tous les usages de `tournament.id`, `match.id`, `round.id`, etc.
- [ ] Identifier tous les props avec `type: Number` pour les IDs
- [ ] Identifier toutes les routes avec des paramètres numériques
- [ ] Identifier les comparaisons numériques d'IDs

### Phase 2: Modification du code
- [ ] Remplacer `id` par `uuid` dans tous les accès aux objets
- [ ] Changer les types de `Number` à `String` pour les IDs
- [ ] Mettre à jour les routes pour utiliser `uuid` au lieu de `id`
- [ ] Mettre à jour les appels API avec les nouveaux chemins
- [ ] Modifier les comparaisons pour utiliser des strings

### Phase 3: Tests
- [ ] Tester toutes les pages de liste de tournois
- [ ] Tester les pages de détail de tournoi
- [ ] Tester l'inscription/désinscription aux tournois
- [ ] Tester la navigation entre les pages
- [ ] Tester le state management (store)
- [ ] Tester les fonctionnalités de matchs et rondes

### Phase 4: LocalStorage/Cache
- [ ] Vider le localStorage existant (anciens IDs)
- [ ] Vérifier que les nouvelles données sont bien stockées
- [ ] Tester la persistance entre les sessions

---

## Exemple complet de migration

### AVANT (Vue.js)

```vue
<template>
  <div>
    <h1>{{ tournament.name }}</h1>
    <p>Organisateur ID: {{ tournament.organizer_id }}</p>
    <button @click="register">S'inscrire</button>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      tournament: null
    }
  },
  mounted() {
    this.fetchTournament();
  },
  methods: {
    async fetchTournament() {
      const response = await axios.get(`/api/tournaments/${this.id}`);
      this.tournament = response.data.tournament;
    },
    async register() {
      await axios.post(`/api/tournaments/${this.id}/register`, {
        game_account_id: 1
      });
    }
  }
}
</script>
```

### MAINTENANT (Vue.js)

```vue
<template>
  <div>
    <h1>{{ tournament.name }}</h1>
    <div v-if="tournament.organizer">
      <p>Organisateur: {{ tournament.organizer.name }}</p>
      <span v-if="tournament.organizer.organizerProfile?.badge"
            class="badge">
        {{ tournament.organizer.organizerProfile.badge }}
      </span>
    </div>

    <!-- Afficher les règles -->
    <div v-if="tournament.rules" class="rules">
      <h3>Règles du tournoi</h3>
      <ul>
        <li v-for="(rule, index) in tournament.rules" :key="index">
          {{ rule }}
        </li>
      </ul>
    </div>

    <button @click="register">S'inscrire</button>
  </div>
</template>

<script>
export default {
  props: {
    uuid: {  // Changé de 'id' à 'uuid'
      type: String,  // Changé de Number à String
      required: true
    }
  },
  data() {
    return {
      tournament: null
    }
  },
  mounted() {
    this.fetchTournament();
  },
  methods: {
    async fetchTournament() {
      const response = await axios.get(`/api/tournaments/${this.uuid}`);
      this.tournament = response.data.tournament;
    },
    async register() {
      await axios.post(`/api/tournaments/${this.uuid}/register`, {
        game_account_id: this.selectedGameAccountId
      });
    }
  }
}
</script>
```

---

## Questions fréquentes (FAQ)

### Q: Pourquoi ce changement?
**R:** Les UUIDs offrent:
- Meilleure sécurité (impossible de deviner les IDs)
- Évite l'énumération des ressources
- Permet une meilleure scalabilité distribuée
- Standard pour les APIs modernes

### Q: Les IDs numériques existent-ils encore?
**R:** Oui, en base de données pour la performance, mais ils ne sont plus exposés via l'API.

### Q: Que se passe-t-il avec les anciennes URLs bookmarkées?
**R:** Les anciennes URLs avec des IDs numériques ne fonctionneront plus. Il faut rediriger les utilisateurs ou afficher un message d'erreur approprié.

### Q: Comment gérer les UUIDs dans les URLs (SEO)?
**R:** Vous pouvez combiner un slug lisible avec l'UUID:
```
/tournaments/tournoi-efootball-decembre-550e8400-e29b-41d4-a716-446655440000
```
Ou utiliser uniquement l'UUID:
```
/tournaments/550e8400-e29b-41d4-a716-446655440000
```

### Q: Les UUIDs sont-ils plus lents?
**R:** Négligeable. Les UUIDs sont indexés en base de données et les performances sont excellentes.

### Q: Comment tester en développement?
**R:** Utilisez les UUIDs retournés par l'API. Vous pouvez aussi utiliser un générateur d'UUID pour les tests:
```javascript
// Pour les tests uniquement
const fakeUuid = "550e8400-e29b-41d4-a716-446655440000";
```

---

## Support

Pour toute question ou problème rencontré durant la migration:
- Créer une issue sur le repository
- Contacter l'équipe backend
- Consulter la documentation de l'API: `/api/documentation`

---

## Changelog

### Version 2.0.0 (2026-01-01)
- Migration complète vers UUIDs pour toutes les entités
- Masquage des IDs internes dans les réponses API
- Ajout du badge de l'organisateur dans les réponses
- Ajout du champ `rules` (JSON) pour les tournois
- Filtrage automatique des tournois privés dans la liste publique
