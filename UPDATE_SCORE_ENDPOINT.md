# Endpoint de Modification de Scores

## 📝 Vue d'ensemble

L'endpoint `update-score` permet aux **organisateurs** et **admins** de modifier les scores d'un match **déjà complété**. Le système recalcule automatiquement tous les éléments impactés (classement, stats, bracket).

## 🔗 Endpoint

```
PATCH /api/matches/{match_uuid}/update-score
```

**Authentification requise:** `Bearer Token`

---

## 👥 Autorisations

- ✅ **Organisateur** du tournoi
- ✅ **Admin**
- ❌ Joueurs, Moderateurs

---

## 📥 Requête

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "player1_score": 3,
  "player2_score": 1
}
```

### Paramètres

| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `player1_score` | integer | ✅ | Score du joueur 1 (≥ 0) |
| `player2_score` | integer | ✅ | Score du joueur 2 (≥ 0) |

---

## 📤 Réponses

### ✅ Succès (200)
```json
{
  "success": true,
  "message": "Scores updated successfully",
  "data": {
    "id": 123,
    "uuid": "abc-123",
    "tournament_id": 5,
    "player1_id": 10,
    "player2_id": 11,
    "player1_score": 3,
    "player2_score": 1,
    "winner_id": 10,
    "status": "completed",
    "player1": {
      "id": 10,
      "uuid": "player-1-uuid",
      "name": "Joueur 1"
    },
    "player2": {
      "id": 11,
      "uuid": "player-2-uuid",
      "name": "Joueur 2"
    },
    "winner": {
      "id": 10,
      "uuid": "player-1-uuid",
      "name": "Joueur 1"
    }
  }
}
```

### ❌ Erreur: Non autorisé (403)
```json
{
  "success": false,
  "message": "Only the tournament organizer or admin can update scores"
}
```

### ❌ Erreur: Match non complété (400)
```json
{
  "success": false,
  "message": "Can only update scores for completed matches"
}
```

### ❌ Erreur: Format Knockout - Match suivant déjà joué (400)
```json
{
  "success": false,
  "message": "Failed to update scores",
  "error": "Cannot change the winner because the next round match has already been played. This would invalidate the tournament bracket."
}
```

### ❌ Erreur: Format Knockout - Draw interdit (400)
```json
{
  "success": false,
  "message": "Failed to update scores",
  "error": "Draws are not allowed in single elimination format. There must be a winner."
}
```

---

## 🎯 Cas d'utilisation Frontend

### 1️⃣ Afficher le bouton "Modifier"

Le bouton doit être visible uniquement si:
- ✅ L'utilisateur est **organisateur** du tournoi OU **admin**
- ✅ Le match a le status `"completed"`

```typescript
canEditScore(match: Match, currentUser: User): boolean {
  const isOrganizer = match.tournament.organizer_id === currentUser.id;
  const isAdmin = currentUser.role === 'admin';
  const isCompleted = match.status === 'completed';

  return (isOrganizer || isAdmin) && isCompleted;
}
```

### 2️⃣ Exemple de composant Angular

```typescript
updateMatchScore(matchUuid: string, player1Score: number, player2Score: number) {
  const url = `${this.apiUrl}/matches/${matchUuid}/update-score`;
  const body = {
    player1_score: player1Score,
    player2_score: player2Score
  };

  return this.http.patch<UpdateScoreResponse>(url, body).pipe(
    tap((response) => {
      if (response.success) {
        // Refresh tournament standings
        this.refreshTournamentStandings(response.data.tournament_id);

        // Show success message
        this.showSuccessMessage('Scores mis à jour avec succès');
      }
    }),
    catchError((error) => {
      // Handle specific errors
      if (error.status === 403) {
        this.showErrorMessage('Vous n\'avez pas la permission de modifier ce match');
      } else if (error.error?.error) {
        this.showErrorMessage(error.error.error);
      } else {
        this.showErrorMessage('Erreur lors de la mise à jour des scores');
      }
      return throwError(() => error);
    })
  );
}
```

### 3️⃣ Interface TypeScript

```typescript
interface UpdateScoreRequest {
  player1_score: number;
  player2_score: number;
}

interface UpdateScoreResponse {
  success: boolean;
  message: string;
  data: {
    id: number;
    uuid: string;
    tournament_id: number;
    player1_id: number;
    player2_id: number;
    player1_score: number;
    player2_score: number;
    winner_id: number;
    status: string;
    player1: {
      id: number;
      uuid: string;
      name: string;
    };
    player2: {
      id: number;
      uuid: string;
      name: string;
    };
    winner: {
      id: number;
      uuid: string;
      name: string;
    } | null;
  };
}
```

### 4️⃣ Exemple de formulaire de modification

```html
<div class="match-card" *ngIf="match.status === 'completed' && canEditScore(match, currentUser)">
  <h3>Match Complété</h3>

  <div class="scores">
    <div class="player">
      <span>{{ match.player1.name }}</span>
      <input type="number"
             [(ngModel)]="editScores.player1"
             min="0"
             [disabled]="!isEditing">
    </div>

    <div class="player">
      <span>{{ match.player2.name }}</span>
      <input type="number"
             [(ngModel)]="editScores.player2"
             min="0"
             [disabled]="!isEditing">
    </div>
  </div>

  <div class="actions">
    <button *ngIf="!isEditing"
            (click)="startEditing(match)"
            class="btn-edit">
      Modifier les scores
    </button>

    <button *ngIf="isEditing"
            (click)="saveScores(match.uuid)"
            class="btn-save">
      Enregistrer
    </button>

    <button *ngIf="isEditing"
            (click)="cancelEditing()"
            class="btn-cancel">
      Annuler
    </button>
  </div>
</div>
```

```typescript
export class MatchCardComponent {
  isEditing = false;
  editScores = { player1: 0, player2: 0 };

  startEditing(match: Match) {
    this.isEditing = true;
    this.editScores.player1 = match.player1_score;
    this.editScores.player2 = match.player2_score;
  }

  cancelEditing() {
    this.isEditing = false;
  }

  saveScores(matchUuid: string) {
    this.matchService
      .updateMatchScore(matchUuid, this.editScores.player1, this.editScores.player2)
      .subscribe({
        next: (response) => {
          this.isEditing = false;
          // Update local match data
          this.match = response.data;
        },
        error: (error) => {
          console.error('Failed to update scores', error);
        }
      });
  }
}
```

---

## ⚙️ Impact Automatique

### Format SWISS 🇨🇭
1. ✅ Annulation des anciennes stats (wins/losses/draws/points)
2. ✅ Application des nouvelles stats
3. ✅ **Recalcul automatique du classement**
4. ✅ Emails envoyés aux joueurs

### Format KNOCKOUT (Élimination directe) 🏆
1. ✅ Vérification: draw interdit
2. ✅ **Protection du bracket**: Si le gagnant change et que le match suivant a déjà été joué → ❌ ERREUR
3. ✅ Si modification autorisée:
   - Retrait de l'ancien gagnant du match suivant
   - Annulation de l'élimination de l'ancien perdant
   - Élimination du nouveau perdant
   - Avancement du nouveau gagnant au match suivant
4. ✅ Emails envoyés aux joueurs

---

## 🔔 Notifications Email

Après modification, les joueurs reçoivent automatiquement un **email de correction de score** (`MatchScoreCorrectedMail`) qui:

- ⚠️ **Alerte** qu'une erreur a été détectée dans les scores
- ❌ Affiche l'**ancien score** (barré, en grisé)
- ✅ Affiche le **nouveau score officiel** (en couleur)
- 🏆 Indique le nouveau résultat: **Victoire**, **Défaite**, ou **Match nul**
- 📊 Précise que le classement et les stats ont été automatiquement mis à jour
- 📢 Mentionne que la correction a été faite par l'organisateur

**Sujet de l'email selon le résultat:**
- Victoire: `✅ Correction de score - Vous avez gagné`
- Défaite: `❌ Correction de score - Vous avez perdu`
- Match nul: `🤝 Correction de score - Match nul` (Swiss uniquement)

---

## ⚠️ Avertissements Frontend

### Pour format KNOCKOUT:
```html
<div class="warning" *ngIf="match.tournament.format === 'single_elimination'">
  ⚠️ <strong>Attention:</strong> Si vous changez le gagnant et que le match suivant
  a déjà été joué, la modification sera refusée pour préserver l'intégrité du bracket.
</div>
```

### Pour draws en KNOCKOUT:
```html
<div class="warning" *ngIf="match.tournament.format === 'single_elimination' && editScores.player1 === editScores.player2">
  ❌ <strong>Erreur:</strong> Les matchs nuls ne sont pas autorisés en élimination directe.
</div>
```

---

## 🔄 Actions après modification

Après une modification réussie, le frontend devrait:

1. ✅ **Rafraîchir le classement** du tournoi
2. ✅ **Mettre à jour l'affichage** du match
3. ✅ **Rafraîchir le bracket** (format knockout)
4. ✅ Afficher un **message de succès**
5. ✅ Éventuellement **notifier** les autres utilisateurs en temps réel (WebSocket)

```typescript
afterScoreUpdate(response: UpdateScoreResponse) {
  // 1. Refresh standings
  this.tournamentService.getStandings(response.data.tournament_id).subscribe();

  // 2. Update match display
  this.match = response.data;

  // 3. Refresh bracket (if knockout)
  if (this.tournament.format === 'single_elimination') {
    this.tournamentService.getBracket(this.tournament.uuid).subscribe();
  }

  // 4. Show success message
  this.toastr.success('Scores mis à jour avec succès');

  // 5. Optional: WebSocket notification
  this.websocketService.emit('match-updated', {
    tournament_id: response.data.tournament_id,
    match_uuid: response.data.uuid
  });
}
```

---

## 🧪 Tests Frontend

```typescript
describe('UpdateScoreComponent', () => {
  it('should show edit button only for organizers and admins', () => {
    // Test authorization logic
  });

  it('should show edit button only for completed matches', () => {
    // Test status check
  });

  it('should prevent draw in knockout format', () => {
    // Test validation
  });

  it('should refresh standings after successful update', () => {
    // Test side effects
  });

  it('should handle bracket validation errors gracefully', () => {
    // Test error handling
  });
});
```

---

## 📞 Support

Pour toute question concernant cet endpoint, contacter l'équipe backend.
