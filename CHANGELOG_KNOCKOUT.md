# Changelog - Format Knockout (Single Elimination)

## Date: 2025-12-25

---

## 🎯 Résumé

Implémentation complète du format **Knockout (Single Elimination)** pour les tournois, avec gestion de l'élimination directe, génération automatique de tous les rounds au démarrage, et notifications par email.

---

## 📋 Table des matières

1. [Nouveaux formats de tournoi](#nouveaux-formats-de-tournoi)
2. [Modifications de la base de données](#modifications-de-la-base-de-données)
3. [Endpoints API modifiés](#endpoints-api-modifiés)
4. [Nouveaux champs dans les réponses](#nouveaux-champs-dans-les-réponses)
5. [Emails envoyés](#emails-envoyés)
6. [Différences Swiss vs Knockout](#différences-swiss-vs-knockout)
7. [Exemples de flux](#exemples-de-flux)
8. [Règles de validation](#règles-de-validation)

---

## 🆕 Nouveaux formats de tournoi

Le système supporte maintenant **3 formats** de tournoi:

| Format | Valeur | Description |
|--------|--------|-------------|
| **Swiss** | `swiss` | Format existant - génération progressive des rounds |
| **Knockout** | `single_elimination` | **NOUVEAU** - Élimination directe, tous les rounds générés au démarrage |
| **Champions League** | `champions_league` | À venir |

---

## 💾 Modifications de la base de données

### Table `rounds`
**Nouveau champ ajouté:**
```json
{
  "round_name": "Quarter-finals" // String, nullable
}
```

**Valeurs possibles:**
- `"Final"` - Finale
- `"Semi-finals"` - Demi-finales
- `"Quarter-finals"` - Quarts de finale
- `"Round of 16"` - 8ème de finale
- `"Round of 32"` - 16ème de finale
- `"Round of 64"` - 32ème de finale
- `"Round 1"`, `"Round 2"`, etc. - Pour les rounds antérieurs

### Table `tournament_registrations`
**Nouveaux champs ajoutés:**
```json
{
  "eliminated": false,           // Boolean - Si le joueur est éliminé
  "eliminated_round": 1,          // Integer, nullable - Round d'élimination
  "eliminated_at": "2025-12-25T..." // DateTime, nullable - Date d'élimination
}
```

### Table `matches`
**Nouveaux champs ajoutés:**
```json
{
  "next_match_id": 15,           // Integer, nullable - ID du prochain match (bracket)
  "bracket_position": 1          // Integer, nullable - Position dans le bracket
}
```

**Modification importante:**
- `player1_id` est maintenant **nullable** (pour les matches placeholders dans le knockout)

---

## 🔌 Endpoints API modifiés

### POST `/api/tournaments/{id}/start`

**Comportement modifié selon le format:**

#### Format Swiss (comportement existant)
- Génère uniquement le **Round 1**
- Les rounds suivants seront générés via `/api/tournaments/{id}/next-round`

#### Format Knockout (nouveau)
- Génère **TOUS les rounds** immédiatement
- Crée la structure complète du bracket
- Les rounds 2+ ont des matches avec `player1_id` et `player2_id` à `null` (placeholders)

**Exemple de réponse pour Knockout (8 participants):**
```json
{
  "message": "Tournament started successfully",
  "round": {
    "id": 1,
    "tournament_id": 10,
    "round_number": 1,
    "round_name": "Quarter-finals",
    "status": "pending",
    "matches": [
      {
        "id": 1,
        "player1_id": 5,
        "player2_id": 8,
        "bracket_position": 1,
        "next_match_id": 5,
        "status": "scheduled"
      },
      {
        "id": 2,
        "player1_id": 3,
        "player2_id": 12,
        "bracket_position": 2,
        "next_match_id": 5,
        "status": "scheduled"
      },
      // ... 2 autres matches du Round 1
    ]
  }
}
```

**Structure complète générée (8 participants):**
```
Round 1 (Quarter-finals): 4 matches avec joueurs réels
Round 2 (Semi-finals):    2 matches avec joueurs null (placeholders)
Round 3 (Final):          1 match avec joueurs null (placeholder)
```

**Erreur spécifique au Knockout:**
```json
{
  "message": "Failed to start tournament",
  "error": "Single elimination requires a power of 2 participants (8, 16, 32, 64)"
}
```

---

### POST `/api/tournaments/{id}/next-round`

**Comportement modifié:**

- ✅ **Swiss**: Fonctionne normalement
- ❌ **Knockout**: **ERREUR 400**

```json
{
  "message": "Failed to generate next round",
  "error": "Manual round generation is only available for Swiss format tournaments"
}
```

**Raison:** En knockout, tous les rounds sont générés automatiquement au démarrage.

---

### POST `/api/matches/{id}/enter-score`

**Comportement modifié selon le format:**

#### Format Swiss
```json
{
  "player1_score": 2,
  "player2_score": 2  // ✅ Nul autorisé
}
```
**Résultat:** Match nul accepté, 1 point pour chaque joueur

#### Format Knockout
```json
{
  "player1_score": 2,
  "player2_score": 2  // ❌ ERREUR
}
```
**Erreur:**
```json
{
  "success": false,
  "message": "Failed to enter scores",
  "error": "Draws are not allowed in single elimination format. There must be a winner."
}
```

**Après un score valide en Knockout:**
```json
{
  "success": true,
  "message": "Score entered successfully",
  "match": {
    "id": 1,
    "status": "completed",
    "winner_id": 5,
    "player1_score": 3,
    "player2_score": 1
  }
}
```

**Effets automatiques:**
1. ✅ Le perdant est **éliminé**
2. ✅ Le gagnant **avance** au prochain match
3. ✅ Le `next_match` est **mis à jour** avec le gagnant
4. ✅ Emails envoyés au gagnant et au perdant

---

## 📧 Emails envoyés

### Format Swiss

| Événement | Email envoyé | Destinataires |
|-----------|--------------|---------------|
| **Tournoi démarré** | `TournamentStartedMail` | **Tous les participants** |
| Score soumis (victoire) | `MatchResultWinnerMail` | Gagnant |
| Score soumis (défaite) | `MatchResultLoserMail` | Perdant |
| **Score soumis (nul)** | `MatchResultDrawMail` | **Les 2 joueurs** |
| Nouveau round généré | `NextRoundGeneratedMail` | Tous les participants |

### Format Knockout

| Événement | Email envoyé | Destinataires |
|-----------|--------------|---------------|
| **Tournoi démarré** | `TournamentStartedMail` | **Tous les participants** |
| Score soumis (victoire) | `MatchResultWinnerMail` | Gagnant |
| Score soumis (défaite) | `MatchResultLoserMail` | Perdant |
| ~~Score soumis (nul)~~ | ❌ **Impossible** | - |
| ~~Nouveau round généré~~ | ❌ **Pas applicable** | - |

**Notes:**

- L'email `TournamentStartedMail` est envoyé dès que le tournoi démarre et informe chaque participant de son premier adversaire
- Pas d'email "Next Round" en Knockout car tous les rounds existent dès le démarrage

---

## 🆚 Différences Swiss vs Knockout

| Caractéristique | Swiss | Knockout |
|----------------|-------|----------|
| **Nombre de participants** | Flexible (≥2) | **Puissance de 2** (8, 16, 32, 64) |
| **Génération des rounds** | Progressive (un à la fois) | **Tous à la fois** au démarrage |
| **Nuls autorisés** | ✅ Oui (1-1-0 points) | ❌ Non (doit y avoir un gagnant) |
| **Élimination** | ❌ Pas d'élimination | ✅ Le perdant est éliminé |
| **Endpoint next-round** | ✅ Disponible | ❌ Interdit |
| **Classement final** | Basé sur les points | Basé sur le round d'élimination |
| **Nombre de rounds** | Défini par l'organisateur | log₂(participants) |

---

## 🔄 Exemples de flux

### Flux complet - Tournoi Knockout 8 joueurs

#### 1. Création du tournoi
```http
POST /api/tournaments
{
  "name": "Knockout Tournament",
  "format": "single_elimination",
  "max_participants": 8,
  "entry_fee": 0,
  "game": "efootball"
}
```

#### 2. Inscription des joueurs (8 joueurs)
```http
POST /api/tournaments/10/register
{
  "game_account_id": 1
}
```
Répéter 8 fois avec différents joueurs.

#### 3. Démarrage du tournoi
```http
POST /api/tournaments/10/start
```

**Réponse:** Structure complète avec 3 rounds
```json
{
  "message": "Tournament started successfully",
  "round": {
    "round_name": "Quarter-finals",
    "matches": [...] // 4 matches avec joueurs réels
  }
}
```

**État après le start:**
```
✅ Round 1 (Quarter-finals): 4 matches - Joueurs assignés
⏸️ Round 2 (Semi-finals):   2 matches - Joueurs null
⏸️ Round 3 (Final):          1 match  - Joueurs null
```

#### 4. Jouer le Round 1 - Match 1
```http
POST /api/matches/1/enter-score
{
  "player1_score": 3,
  "player2_score": 1
}
```

**Effets automatiques:**
- ✅ Match 1 marqué comme `completed`
- ✅ `winner_id` = player1
- ✅ Player 2 éliminé:
  ```json
  {
    "eliminated": true,
    "eliminated_round": 1,
    "eliminated_at": "2025-12-25T..."
  }
  ```
- ✅ Player 1 avancé au match 5 (Semi-finale):
  ```json
  // Match 5 (Semi-finals)
  {
    "player1_id": 5,  // ← Mis à jour automatiquement
    "player2_id": null
  }
  ```

#### 5. Compléter tous les matches du Round 1
Après avoir joué les 4 matches:
```
✅ Round 1 (Quarter-finals): 4 matches - COMPLETED
✅ Round 2 (Semi-finals):    2 matches - Joueurs assignés
⏸️ Round 3 (Final):          1 match  - Joueurs null
```

#### 6. Jouer les Semi-finales et la Finale
Continuer jusqu'à ce que tous les matches soient terminés.

#### 7. Compléter le tournoi
```http
POST /api/tournaments/10/complete
```

**Résultat:**
```json
{
  "message": "Tournament completed successfully",
  "rankings": [
    {
      "user_id": 5,
      "final_rank": 1,
      "eliminated": false
    },
    {
      "user_id": 8,
      "final_rank": 2,
      "eliminated": true,
      "eliminated_round": 3  // Éliminé en finale
    },
    {
      "user_id": 3,
      "final_rank": 3,
      "eliminated": true,
      "eliminated_round": 2  // Éliminé en semi
    },
    {
      "user_id": 12,
      "final_rank": 3,
      "eliminated": true,
      "eliminated_round": 2  // Éliminé en semi
    }
    // ... Les 4 autres ont final_rank entre 5-8
  ]
}
```

---

## ✅ Règles de validation

### Création de tournoi Knockout
```javascript
// Frontend validation
if (format === 'single_elimination') {
  const validCounts = [8, 16, 32, 64];
  if (!validCounts.includes(max_participants)) {
    alert('Knockout requires 8, 16, 32, or 64 participants');
  }
}
```

### Démarrage de tournoi
```javascript
// Swiss: Au moins 2 participants
if (format === 'swiss' && registeredCount < 2) {
  alert('Need at least 2 participants');
}

// Knockout: Exactement une puissance de 2
if (format === 'single_elimination') {
  const validCounts = [8, 16, 32, 64];
  if (!validCounts.includes(registeredCount)) {
    alert(`Need exactly ${nearest power of 2} participants`);
  }
}
```

### Soumission de score
```javascript
// Knockout: Interdire les nuls
if (format === 'single_elimination' && player1Score === player2Score) {
  alert('Draws not allowed in knockout format. There must be a winner.');
}
```

### Interface du bracket
```javascript
// Afficher les matches placeholders différemment
function renderMatch(match) {
  if (match.player1_id === null || match.player2_id === null) {
    return <MatchPlaceholder text="Winner of..." />;
  }
  return <Match player1={match.player1} player2={match.player2} />;
}
```

---

## 📊 Nouvelles données disponibles

### GET `/api/tournaments/{id}`

**Nouveaux champs dans la réponse:**
```json
{
  "id": 10,
  "format": "single_elimination",
  "total_rounds": 3,
  "rounds": [
    {
      "round_number": 1,
      "round_name": "Quarter-finals",
      "matches": [
        {
          "next_match_id": 5,
          "bracket_position": 1
        }
      ]
    },
    {
      "round_number": 2,
      "round_name": "Semi-finals"
    },
    {
      "round_number": 3,
      "round_name": "Final"
    }
  ],
  "registrations": [
    {
      "user_id": 5,
      "eliminated": true,
      "eliminated_round": 2,
      "eliminated_at": "2025-12-25T..."
    }
  ]
}
```

---

## 🎨 Recommandations UI/UX

### 1. Page de création de tournoi
```javascript
// Afficher un avertissement pour le knockout
{format === 'single_elimination' && (
  <Alert type="info">
    Format Knockout: Le nombre de participants doit être
    exactement 8, 16, 32 ou 64. Tous les rounds seront
    générés automatiquement au démarrage.
  </Alert>
)}
```

### 2. Page du tournoi
```javascript
// Afficher différemment selon le format
{tournament.format === 'single_elimination' ? (
  <BracketView rounds={tournament.rounds} />
) : (
  <RoundByRoundView rounds={tournament.rounds} />
)}
```

### 3. Badge d'élimination
```javascript
// Afficher si un joueur est éliminé
{registration.eliminated && (
  <Badge color="red">
    Eliminated in {registration.eliminated_round === 3 ? 'Final' :
                   registration.eliminated_round === 2 ? 'Semi-finals' :
                   'Quarter-finals'}
  </Badge>
)}
```

### 4. Saisie de score
```javascript
// Validation côté client
function validateScore(player1Score, player2Score, format) {
  if (format === 'single_elimination' && player1Score === player2Score) {
    throw new Error('Draws not allowed in knockout format');
  }
}
```

### 5. Bouton "Next Round" conditionnel
```javascript
// Cacher le bouton pour les tournois knockout
{tournament.format === 'swiss' && (
  <Button onClick={generateNextRound}>
    Generate Next Round
  </Button>
)}
```

---

## 🐛 Cas particuliers à gérer

### 1. Match avec joueurs null
```javascript
// Ne pas afficher le bouton "Enter Score" si les joueurs ne sont pas assignés
function canEnterScore(match) {
  return match.player1_id !== null && match.player2_id !== null;
}
```

### 2. Tournoi knockout incomplet
```javascript
// Si un tournoi knockout démarre avec 7 joueurs (erreur)
// L'API retournera une erreur 400
// Afficher un message clair à l'utilisateur
```

### 3. Classement final
```javascript
// En knockout, les joueurs éliminés au même round ont le même rang
// Ex: Les 2 perdants des semi-finales sont tous les deux 3ème
```

---

## 📞 Support

Pour toute question sur l'intégration:
1. Consulter ce document
2. Vérifier les tests dans `tests/Feature/KnockoutTournamentTest.php`
3. Tester avec l'API en environnement de développement

---

## ✨ Améliorations futures possibles

- [ ] Système de "bye" pour les tournois avec nombre impair de participants
- [ ] Génération automatique de bracket visuel (SVG)
- [ ] Support du format "Double Elimination"
- [ ] Repêchage pour les perdants
- [ ] Format "Champions League" (phase de groupes + knockout)

---

**Dernière mise à jour:** 2025-12-25
**Version API:** 1.0
