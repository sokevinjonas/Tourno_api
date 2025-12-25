# Endpoint - Compléter un tournoi et distribuer les prix

## 📌 Résumé

Cet endpoint permet de **finaliser un tournoi** après que tous les matchs ont été joués. Il calcule automatiquement le classement final, distribue les prix depuis le wallet bloqué de l'organisateur vers les gagnants, et retourne le reste des fonds à l'organisateur.

---

## 🔌 Informations de base

| Propriété | Valeur |
|-----------|--------|
| **Méthode** | `POST` |
| **URL** | `/api/tournaments/{id}/complete` |
| **Authentification** | ✅ Requise (Bearer Token) |
| **Autorisation** | Organisateur du tournoi OU Admin |

---

## 📥 Requête

### URL Parameters

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | `integer` | ID du tournoi à compléter |

### Headers

```http
Authorization: Bearer {token}
Content-Type: application/json
```

### Body

**Aucun body requis** - L'endpoint ne prend aucun paramètre dans le body.

### Exemple de requête

```javascript
// JavaScript/React
const completeTournament = async (tournamentId) => {
  const response = await fetch(`/api/tournaments/${tournamentId}/complete`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });

  return await response.json();
};
```

```bash
# cURL
curl -X POST https://api.example.com/api/tournaments/10/complete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## 📤 Réponses

### ✅ Succès (200 OK)

```json
{
  "message": "Tournament completed successfully. Prizes have been distributed.",
  "tournament": {
    "id": 10,
    "name": "FIFA Championship",
    "format": "single_elimination",
    "status": "completed",
    "entry_fee": 10,
    "prize_pool": 80,
    "prize_distribution": {
      "1": 50,
      "2": 20,
      "3": 10
    },
    "organizer_id": 1,
    "created_at": "2025-12-20T10:00:00.000000Z",
    "updated_at": "2025-12-25T14:30:00.000000Z",
    "registrations": [
      {
        "id": 1,
        "user_id": 5,
        "tournament_id": 10,
        "status": "registered",
        "final_rank": 1,
        "prize_won": 50,
        "tournament_points": 9,
        "wins": 3,
        "losses": 0,
        "draws": 0,
        "eliminated": false,
        "eliminated_round": null,
        "eliminated_at": null
      },
      {
        "id": 2,
        "user_id": 8,
        "tournament_id": 10,
        "status": "registered",
        "final_rank": 2,
        "prize_won": 20,
        "tournament_points": 6,
        "wins": 2,
        "losses": 1,
        "draws": 0,
        "eliminated": true,
        "eliminated_round": 3,
        "eliminated_at": "2025-12-25T14:15:00.000000Z"
      },
      {
        "id": 3,
        "user_id": 3,
        "tournament_id": 10,
        "status": "registered",
        "final_rank": 3,
        "prize_won": 10,
        "tournament_points": 3,
        "wins": 1,
        "losses": 1,
        "draws": 0,
        "eliminated": true,
        "eliminated_round": 2,
        "eliminated_at": "2025-12-25T13:45:00.000000Z"
      },
      {
        "id": 4,
        "user_id": 12,
        "tournament_id": 10,
        "status": "registered",
        "final_rank": 3,
        "prize_won": 0,
        "tournament_points": 3,
        "wins": 1,
        "losses": 1,
        "draws": 0,
        "eliminated": true,
        "eliminated_round": 2,
        "eliminated_at": "2025-12-25T13:50:00.000000Z"
      }
    ]
  }
}
```

**Champs importants de la réponse:**

| Champ | Type | Description |
|-------|------|-------------|
| `tournament.status` | `string` | Toujours `"completed"` après succès |
| `registrations[].final_rank` | `integer` | Rang final du joueur (1 = champion) |
| `registrations[].prize_won` | `float` | Montant du prix gagné en pièces |
| `registrations[].eliminated` | `boolean` | Si le joueur a été éliminé (Knockout uniquement) |
| `registrations[].eliminated_round` | `integer\|null` | Round d'élimination (Knockout uniquement) |

---

### ❌ Erreurs possibles

#### 1. Tournoi non trouvé (404)

```json
{
  "message": "Tournament not found"
}
```

#### 2. Non autorisé (403)

```json
{
  "message": "Unauthorized"
}
```

**Cause:** L'utilisateur n'est ni l'organisateur ni un admin.

#### 3. Matchs non terminés (400)

```json
{
  "message": "Failed to complete tournament",
  "error": "Cannot complete tournament while 3 match(es) are still pending"
}
```

**Cause:** Des matchs ont encore le statut `scheduled`, `in_progress`, `pending_validation`, ou `disputed`.

**Action:** Assurez-vous que tous les matchs sont en statut `completed` avant de compléter le tournoi.

#### 4. Format non supporté (400)

```json
{
  "message": "Failed to complete tournament",
  "error": "Unsupported tournament format: champions_league"
}
```

---

## 🔄 Fonctionnement détaillé

### Ce que fait l'endpoint automatiquement :

1. **Vérification des matchs**
   - Vérifie que TOUS les matchs du tournoi sont terminés (statut `completed`)
   - Si des matchs sont en attente → Erreur 400

2. **Calcul du classement final**

   **Format Swiss:**
   - Tri par `tournament_points` (décroissant)
   - Puis par `wins` (décroissant)
   - Puis par `draws` (décroissant)

   **Format Knockout:**
   - Tri par `eliminated = false` d'abord (le champion)
   - Puis par `eliminated_round` (décroissant)
   - Les joueurs éliminés au même round ont le même rang

3. **Distribution des prix**

   Utilise `prize_distribution` du tournoi:
   ```json
   {
     "1": 50,    // 1er place reçoit 50 pièces
     "2": 20,    // 2e place reçoit 20 pièces
     "3": 10     // 3e place reçoit 10 pièces
   }
   ```

   **⚠️ IMPORTANT:** Les valeurs sont des **montants absolus en pièces**, PAS des pourcentages!

4. **Gestion des wallets**

   - Débite le `blocked_balance` de l'organisateur
   - Crédite le `balance` de chaque gagnant
   - Retourne le reste du `blocked_balance` au `balance` de l'organisateur
   - Toutes les opérations sont effectuées dans une transaction atomique

5. **Mise à jour des données**

   - Met à jour `tournament.status` → `"completed"`
   - Met à jour `registrations[].final_rank` pour tous les participants
   - Met à jour `registrations[].prize_won` pour les gagnants

---

## 💡 Recommandations UI/UX

### 1. Vérification avant complétion

```javascript
// Vérifier que tous les matchs sont terminés avant d'afficher le bouton
const canCompleteTournament = (tournament) => {
  const allMatches = tournament.rounds.flatMap(r => r.matches);
  const pendingMatches = allMatches.filter(m =>
    ['scheduled', 'in_progress', 'pending_validation', 'disputed'].includes(m.status)
  );

  return pendingMatches.length === 0;
};
```

### 2. Bouton de complétion conditionnel

```jsx
{canCompleteTournament(tournament) ? (
  <Button
    onClick={() => completeTournament(tournament.id)}
    variant="success"
  >
    Terminer le tournoi et distribuer les prix
  </Button>
) : (
  <Alert type="warning">
    {pendingMatchesCount} match(es) doivent être terminés avant de compléter le tournoi.
  </Alert>
)}
```

### 3. Affichage du classement final

```jsx
// Après complétion, afficher le classement avec les prix
const RankingTable = ({ registrations }) => (
  <table>
    <thead>
      <tr>
        <th>Rang</th>
        <th>Joueur</th>
        <th>Points</th>
        <th>Prix gagné</th>
      </tr>
    </thead>
    <tbody>
      {registrations
        .sort((a, b) => a.final_rank - b.final_rank)
        .map(reg => (
          <tr key={reg.id}>
            <td>
              {reg.final_rank === 1 && '🥇'}
              {reg.final_rank === 2 && '🥈'}
              {reg.final_rank === 3 && '🥉'}
              #{reg.final_rank}
            </td>
            <td>{reg.user.name}</td>
            <td>{reg.tournament_points} pts</td>
            <td>
              {reg.prize_won > 0 ? (
                <strong>{reg.prize_won} MLM</strong>
              ) : (
                '-'
              )}
            </td>
          </tr>
        ))}
    </tbody>
  </table>
);
```

### 4. Confirmation avant complétion

```javascript
const handleCompleteTournament = async (tournamentId) => {
  const confirmed = await showConfirmDialog({
    title: 'Terminer le tournoi ?',
    message: 'Cette action va distribuer les prix et ne peut pas être annulée.',
    confirmText: 'Terminer et distribuer',
    cancelText: 'Annuler'
  });

  if (!confirmed) return;

  try {
    const result = await completeTournament(tournamentId);
    showSuccessMessage('Tournoi terminé ! Les prix ont été distribués.');
    // Rafraîchir les données du tournoi
    refreshTournamentData();
  } catch (error) {
    showErrorMessage(error.message || 'Erreur lors de la complétion du tournoi');
  }
};
```

---

## 🧪 Scénarios de test

### Test 1: Complétion réussie (Swiss - 4 joueurs)

**Setup:**
- Tournoi Swiss avec 4 participants
- `prize_distribution`: `{"1": 60, "2": 30, "3": 10}`
- Tous les matchs terminés

**Résultat attendu:**
- Status 200
- Joueur 1er: reçoit 60 pièces
- Joueur 2e: reçoit 30 pièces
- Joueur 3e: reçoit 10 pièces
- Organisateur: reçoit le reste (entry_fees - 100)

### Test 2: Complétion réussie (Knockout - 8 joueurs)

**Setup:**
- Tournoi Knockout avec 8 participants
- `prize_distribution`: `{"1": 50, "2": 20, "3": 10, "4": 10}`
- Tous les matchs terminés

**Résultat attendu:**
- Status 200
- Champion (eliminated=false): reçoit 50 pièces
- Finaliste (eliminated_round=3): reçoit 20 pièces
- 2 demi-finalistes (eliminated_round=2): reçoivent 10 pièces chacun
- Les 2 joueurs à la 3e place ont `final_rank = 3`

### Test 3: Erreur - Matchs non terminés

**Setup:**
- 2 matchs encore en statut `pending_validation`

**Résultat attendu:**
- Status 400
- Message: "Cannot complete tournament while 2 match(es) are still pending"

### Test 4: Erreur - Utilisateur non autorisé

**Setup:**
- Utilisateur qui n'est ni l'organisateur ni admin

**Résultat attendu:**
- Status 403
- Message: "Unauthorized"

---

## 📊 Exemple de flux complet

```javascript
// 1. Charger le tournoi
const tournament = await fetchTournament(10);

// 2. Vérifier que tous les matchs sont terminés
const canComplete = checkAllMatchesCompleted(tournament);

if (!canComplete) {
  alert('Tous les matchs doivent être terminés avant de compléter le tournoi.');
  return;
}

// 3. Afficher confirmation avec preview des prix
const prizePreview = calculatePrizePreview(
  tournament.registrations,
  tournament.prize_distribution
);

const confirmed = await showPrizeDistributionDialog(prizePreview);

if (!confirmed) return;

// 4. Compléter le tournoi
try {
  setLoading(true);

  const response = await fetch(`/api/tournaments/${tournament.id}/complete`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.error || error.message);
  }

  const result = await response.json();

  // 5. Afficher le classement final et les prix distribués
  showTournamentResults(result.tournament);

  // 6. Rediriger vers la page du tournoi complété
  navigate(`/tournaments/${tournament.id}/results`);

} catch (error) {
  showErrorMessage(error.message);
} finally {
  setLoading(false);
}
```

---

## ⚠️ Points d'attention

### 1. Distribution des prix

- Les valeurs dans `prize_distribution` sont des **montants absolus** en pièces
- Si un rang n'est pas dans `prize_distribution`, le joueur ne reçoit rien
- En Knockout, plusieurs joueurs peuvent avoir le même `final_rank` (ex: 2 joueurs éliminés en demi-finales = 3e place)

### 2. Gestion du wallet

- Les fonds sont prélevés du `blocked_balance` de l'organisateur (déjà bloqués au démarrage du tournoi)
- Le reste des fonds retourne automatiquement au `balance` de l'organisateur
- Toutes les transactions sont atomiques (tout réussit ou tout échoue)

### 3. Statuts des matchs

Les statuts suivants bloquent la complétion:
- `scheduled` - Match programmé mais pas encore joué
- `in_progress` - Match en cours
- `pending_validation` - En attente de validation par l'organisateur
- `disputed` - Match contesté

Seul le statut `completed` permet la complétion du tournoi.

### 4. Permissions

Seuls l'**organisateur** du tournoi OU un **admin** peuvent compléter le tournoi.

---

## 🔗 Endpoints liés

| Endpoint | Description |
|----------|-------------|
| `GET /api/tournaments/{id}` | Récupérer les détails du tournoi |
| `GET /api/tournaments/{id}/rounds` | Récupérer tous les rounds et matchs |
| `POST /api/tournaments/{id}/start` | Démarrer le tournoi |
| `POST /api/matches/{id}/enter-score` | Soumettre un score de match |

---

**Dernière mise à jour:** 2025-12-25
**Version API:** 1.0
