# Documentation Système de Tournois - API Backend

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Workflow de création de tournoi](#workflow-de-création-de-tournoi)
3. [Statuts des tournois](#statuts-des-tournois)
4. [Endpoints API](#endpoints-api)
5. [Système de solde bloqué](#système-de-solde-bloqué)
6. [Auto-Management](#auto-management)
7. [Chat et Preuves](#chat-et-preuves)
8. [Emails automatiques](#emails-automatiques)

---

## Vue d'ensemble

Le système de tournois permet aux organisateurs (avec badge certified, verified ou partner) de créer et gérer des tournois. Les tournois peuvent être:
- **Public**: Visibles sur la plateforme
- **Private**: Accessibles uniquement via URL unique

### Frais de création
- **Certified badge**: 2 MLM par tournoi
- **Verified/Partner badge**: 0 MLM (gratuit)

### Système de protection
- **Solde bloqué**: Les frais d'inscription sont bloqués dans le wallet de l'organisateur jusqu'à la fin du tournoi
- **Auto-refund**: Remboursement automatique si tournoi plein mais non démarré après 48h

---

## Workflow de création de tournoi

```
1. Organisateur crée le tournoi (statut: draft)
   ↓
2. Paiement des frais de création (2 MLM si certified, 0 si verified/partner)
   ↓
3. Tournoi publié (statut: open)
   ↓
4. Joueurs s'inscrivent (paient entry_fee)
   ↓
5a. MANUEL: Organisateur démarre le tournoi (statut: in_progress)
5b. AUTO: Système démarre automatiquement (si auto_managed=true et conditions remplies)
   ↓
6. Blocage des fonds de l'organisateur (total des entry_fees)
   ↓
7. Génération des matches du 1er tour
   ↓
8. Emails envoyés aux participants avec info adversaire
   ↓
9. Joueurs jouent leurs matches (chat, upload preuves)
   ↓
10. Organisateur entre les scores
   ↓
11. Tour suivant généré (ou tournoi terminé)
   ↓
12. Distribution des prix aux gagnants
   ↓
13. Déblocage des fonds restants pour l'organisateur
```

---

## Statuts des tournois

| Statut | Description |
|--------|-------------|
| `draft` | Tournoi créé mais non publié |
| `open` | Inscriptions ouvertes |
| `in_progress` | Tournoi en cours |
| `completed` | Tournoi terminé, distribution en cours |
| `payout_pending` | En attente de paiement des gagnants |
| `payouts_completed` | Tous les paiements effectués |
| `cancelled` | Tournoi annulé (ex: remboursement 48h) |

---

## Endpoints API

### 1. Créer un tournoi

**Endpoint:** `POST /api/tournaments`
**Auth:** Required (Organizer/Admin)

**Request Body:**
```json
{
  "name": "Championship FIFA Mobile 2025",
  "description": "Tournoi hebdomadaire FIFA Mobile",
  "game_type": "fc_mobile",
  "format": "single_elimination",
  "max_participants": 16,
  "entry_fee": 5.00,
  "prize_pool": 80.00,
  "prize_distribution": {
    "1": 50,
    "2": 30,
    "3": 20
  },
  "visibility": "public",
  "auto_managed": true,
  "registration_start": "2025-12-22 10:00:00",
  "registration_end": "2025-12-24 18:00:00",
  "start_date": "2025-12-25 14:00:00",
  "end_date": "2025-12-25 20:00:00",
  "rules": "Bo3 format, screenshots required"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Tournament created successfully",
  "data": {
    "id": 1,
    "organizer_id": 5,
    "name": "Championship FIFA Mobile 2025",
    "description": "Tournoi hebdomadaire FIFA Mobile",
    "game_type": "fc_mobile",
    "format": "single_elimination",
    "max_participants": 16,
    "entry_fee": "5.00",
    "prize_pool": "80.00",
    "prize_distribution": {
      "1": 50,
      "2": 30,
      "3": 20
    },
    "status": "open",
    "visibility": "public",
    "unique_url": null,
    "creation_fee_paid": "2.00",
    "full_since": null,
    "auto_managed": true,
    "registration_start": "2025-12-22T10:00:00.000000Z",
    "registration_end": "2025-12-24T18:00:00.000000Z",
    "start_date": "2025-12-25T14:00:00.000000Z",
    "actual_start_date": null,
    "end_date": "2025-12-25T20:00:00.000000Z",
    "rules": "Bo3 format, screenshots required",
    "created_at": "2025-12-21T16:00:00.000000Z",
    "updated_at": "2025-12-21T16:00:00.000000Z"
  }
}
```

**Notes:**
- `unique_url` sera généré automatiquement si `visibility` = "private"
- `creation_fee_paid` dépend du badge de l'organisateur (2 MLM ou 0)

---

### 2. Lister tous les tournois publics

**Endpoint:** `GET /api/tournaments`
**Auth:** Not required

**Query Parameters:**
- `status` (optional): `open`, `in_progress`, `completed`
- `game_type` (optional): `efootball`, `fc_mobile`, `dream_league_soccer`
- `sort` (optional): `asc`, `desc` (default: `desc`)

**Response:** `200 OK`
```json
{
  "tournaments": [
    {
      "id": 1,
      "name": "Championship FIFA Mobile 2025",
      "game_type": "fc_mobile",
      "format": "single_elimination",
      "max_participants": 16,
      "entry_fee": "5.00",
      "prize_pool": "80.00",
      "status": "open",
      "visibility": "public",
      "start_date": "2025-12-25T14:00:00.000000Z",
      "organizer": {
        "id": 5,
        "name": "John Organizer",
        "email": "john@example.com"
      },
      "registrations_count": 8
    }
  ],
  "total": 1
}
```

---

### 3. Voir un tournoi spécifique

**Endpoint:** `GET /api/tournaments/{id}`
**Auth:** Not required

**Response:** `200 OK`
```json
{
  "id": 1,
  "name": "Championship FIFA Mobile 2025",
  "description": "Tournoi hebdomadaire FIFA Mobile",
  "game_type": "fc_mobile",
  "format": "single_elimination",
  "max_participants": 16,
  "entry_fee": "5.00",
  "prize_pool": "80.00",
  "prize_distribution": {
    "1": 50,
    "2": 30,
    "3": 20
  },
  "status": "open",
  "visibility": "public",
  "registration_start": "2025-12-22T10:00:00.000000Z",
  "registration_end": "2025-12-24T18:00:00.000000Z",
  "start_date": "2025-12-25T14:00:00.000000Z",
  "organizer": {
    "id": 5,
    "name": "John Organizer",
    "email": "john@example.com"
  },
  "registrations": [
    {
      "id": 1,
      "user": {
        "id": 10,
        "name": "Player One",
        "email": "player1@example.com"
      },
      "status": "registered",
      "registered_at": "2025-12-22T12:00:00.000000Z"
    }
  ],
  "rounds": [],
  "matches": []
}
```

---

### 4. S'inscrire à un tournoi

**Endpoint:** `POST /api/tournaments/{id}/register`
**Auth:** Required

**Request Body:**
```json
{
  "game_account_id": 3
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Successfully registered for tournament",
  "data": {
    "id": 1,
    "tournament_id": 1,
    "user_id": 10,
    "game_account_id": 3,
    "status": "registered",
    "registered_at": "2025-12-22T12:00:00.000000Z"
  }
}
```

**Effets secondaires:**
- Entry fee débité du wallet de l'utilisateur
- Email de confirmation envoyé
- Si tournoi devient plein → `full_since` timestamp enregistré

---

### 5. Se désinscrire d'un tournoi

**Endpoint:** `POST /api/tournaments/{id}/withdraw`
**Auth:** Required

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Successfully withdrawn from tournament"
}
```

**Effets secondaires:**
- Entry fee remboursé sur le wallet
- Inscription supprimée ou marquée comme "withdrawn"

---

### 6. Voir les participants d'un tournoi

**Endpoint:** `GET /api/tournaments/{id}/participants`
**Auth:** Required

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {
        "id": 10,
        "name": "Player One",
        "email": "player1@example.com"
      },
      "game_account": {
        "id": 3,
        "game_type": "fc_mobile",
        "game_username": "ProPlayer123"
      },
      "status": "registered",
      "registered_at": "2025-12-22T12:00:00.000000Z"
    }
  ]
}
```

---

### 7. Démarrer un tournoi (Manuel)

**Endpoint:** `POST /api/tournaments/{id}/start`
**Auth:** Required (Organizer/Admin)

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Tournament started successfully",
  "data": {
    "tournament": {
      "id": 1,
      "status": "in_progress",
      "actual_start_date": "2025-12-25T14:00:00.000000Z"
    },
    "round": {
      "id": 1,
      "round_number": 1,
      "status": "in_progress"
    },
    "matches_created": 8
  }
}
```

**Effets secondaires:**
- Statut → `in_progress`
- `actual_start_date` enregistré
- Fonds de l'organisateur bloqués dans `tournament_wallet_locks`
- Premier tour créé avec matches
- Emails envoyés à tous les participants avec info adversaire

---

### 8. Obtenir les matches d'un tournoi

**Endpoint:** `GET /api/tournaments/{id}/rounds`
**Auth:** Required

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tournament_id": 1,
      "round_number": 1,
      "status": "in_progress",
      "start_date": "2025-12-25T14:00:00.000000Z",
      "end_date": null,
      "matches": [
        {
          "id": 1,
          "tournament_id": 1,
          "round_id": 1,
          "player1": {
            "id": 10,
            "name": "Player One"
          },
          "player2": {
            "id": 11,
            "name": "Player Two"
          },
          "player1_score": null,
          "player2_score": null,
          "winner_id": null,
          "status": "scheduled",
          "scheduled_at": null,
          "completed_at": null
        }
      ]
    }
  ]
}
```

---

### 9. Voir un match spécifique

**Endpoint:** `GET /api/matches/{id}`
**Auth:** Required

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "tournament": {
      "id": 1,
      "name": "Championship FIFA Mobile 2025"
    },
    "round": {
      "id": 1,
      "round_number": 1
    },
    "player1": {
      "id": 10,
      "name": "Player One",
      "email": "player1@example.com"
    },
    "player2": {
      "id": 11,
      "name": "Player Two",
      "email": "player2@example.com"
    },
    "player1_score": null,
    "player2_score": null,
    "winner_id": null,
    "status": "scheduled",
    "scheduled_at": null,
    "completed_at": null,
    "created_at": "2025-12-25T14:00:00.000000Z"
  }
}
```

---

## Chat et Preuves

### 10. Envoyer un message dans le chat du match

**Endpoint:** `POST /api/matches/{id}/messages`
**Auth:** Required (Participant only)

**Request Body:**
```json
{
  "message": "Hey! When do you want to play?"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 1,
    "match_id": 1,
    "user_id": 10,
    "message": "Hey! When do you want to play?",
    "read_at": null,
    "created_at": "2025-12-25T14:30:00.000000Z",
    "user": {
      "id": 10,
      "name": "Player One",
      "email": "player1@example.com"
    }
  }
}
```

**Effets secondaires:**
- Email automatique envoyé à l'adversaire

---

### 11. Récupérer les messages d'un match

**Endpoint:** `GET /api/matches/{id}/messages`
**Auth:** Required (Participant, Organizer, Admin)

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "match_id": 1,
      "user": {
        "id": 10,
        "name": "Player One",
        "email": "player1@example.com"
      },
      "message": "Hey! When do you want to play?",
      "read_at": "2025-12-25T14:35:00.000000Z",
      "created_at": "2025-12-25T14:30:00.000000Z"
    },
    {
      "id": 2,
      "match_id": 1,
      "user": {
        "id": 11,
        "name": "Player Two",
        "email": "player2@example.com"
      },
      "message": "In 30 minutes?",
      "read_at": null,
      "created_at": "2025-12-25T14:32:00.000000Z"
    }
  ]
}
```

**Effets secondaires:**
- Messages non lus marqués comme lus pour l'utilisateur actuel

---

### 12. Upload une preuve (screenshot)

**Endpoint:** `POST /api/matches/{id}/evidence`
**Auth:** Required (Participant only)
**Content-Type:** `multipart/form-data`

**Request Body:**
```
file: [File] (JPG, PNG, PDF - Max 5MB)
type: "screenshot" | "proof" | "result"
description: "Final score screenshot" (optional)
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Evidence uploaded successfully",
  "data": {
    "id": 1,
    "match_id": 1,
    "user_id": 10,
    "file_path": "evidence/matches/1/abc123.jpg",
    "type": "screenshot",
    "description": "Final score screenshot",
    "created_at": "2025-12-25T15:00:00.000000Z",
    "user": {
      "id": 10,
      "name": "Player One",
      "email": "player1@example.com"
    }
  }
}
```

**Notes:**
- Fichiers stockés dans `storage/app/public/evidence/matches/{matchId}/`
- URL publique: `{APP_URL}/storage/evidence/matches/{matchId}/filename.jpg`

---

### 13. Récupérer les preuves d'un match

**Endpoint:** `GET /api/matches/{id}/evidence`
**Auth:** Required (Participant, Organizer, Admin)

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "match_id": 1,
      "user": {
        "id": 10,
        "name": "Player One",
        "email": "player1@example.com"
      },
      "file_path": "evidence/matches/1/abc123.jpg",
      "type": "screenshot",
      "description": "Final score screenshot",
      "created_at": "2025-12-25T15:00:00.000000Z"
    }
  ]
}
```

---

### 14. Entrer les scores d'un match (Organisateur)

**Endpoint:** `POST /api/matches/{id}/enter-score`
**Auth:** Required (Organizer/Admin only)

**Request Body:**
```json
{
  "player1_score": 3,
  "player2_score": 1
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Scores entered successfully",
  "data": {
    "id": 1,
    "tournament_id": 1,
    "round_id": 1,
    "player1": {
      "id": 10,
      "name": "Player One"
    },
    "player2": {
      "id": 11,
      "name": "Player Two"
    },
    "player1_score": 3,
    "player2_score": 1,
    "winner": {
      "id": 10,
      "name": "Player One"
    },
    "status": "completed",
    "completed_at": "2025-12-25T15:30:00.000000Z"
  }
}
```

**Notes:**
- Le gagnant est déterminé automatiquement
- Si scores égaux, `winner_id` = null (match nul)
- Statut du match → `completed`

---

### 15. Mes tournois (Organisateur)

**Endpoint:** `GET /api/tournaments/my/tournaments`
**Auth:** Required (Organizer/Admin)

**Response:** `200 OK`
```json
{
  "tournaments": [
    {
      "id": 1,
      "name": "Championship FIFA Mobile 2025",
      "status": "in_progress",
      "registrations_count": 16,
      "created_at": "2025-12-21T16:00:00.000000Z"
    }
  ]
}
```

---

### 16. Mes inscriptions (Joueur)

**Endpoint:** `GET /api/my/registrations`
**Auth:** Required

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tournament": {
        "id": 1,
        "name": "Championship FIFA Mobile 2025",
        "game_type": "fc_mobile",
        "status": "in_progress",
        "start_date": "2025-12-25T14:00:00.000000Z"
      },
      "status": "registered",
      "registered_at": "2025-12-22T12:00:00.000000Z"
    }
  ]
}
```

---

### 17. Mes matches (Joueur)

**Endpoint:** `GET /api/matches/my/matches`
**Auth:** Required

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tournament": {
        "id": 1,
        "name": "Championship FIFA Mobile 2025"
      },
      "opponent": {
        "id": 11,
        "name": "Player Two"
      },
      "my_score": 3,
      "opponent_score": 1,
      "status": "completed",
      "is_winner": true
    }
  ]
}
```

---

## Système de solde bloqué

Lorsqu'un tournoi démarre, les fonds sont automatiquement bloqués pour éviter la fraude:

### Table: `tournament_wallet_locks`

```
id: 1
tournament_id: 1
wallet_id: 5 (wallet de l'organisateur)
locked_amount: 80.00 (16 participants × 5.00 entry_fee)
status: "locked" | "processing_payouts" | "released"
paid_out: 0.00
```

### Processus:
1. **Tournoi démarre** → Fonds bloqués (`status: locked`)
2. **Distribution prix** → `status: processing_payouts`, `paid_out` augmente
3. **Fonds libérés** → `status: released`, fonds restants ajoutés au solde normal

---

## Auto-Management

Si `auto_managed: true`, le système démarre automatiquement le tournoi quand:
- Nombre de participants = `max_participants`
- Date actuelle ≥ `start_date`
- Statut = `open`

**Job:** `AutoStartTournamentsJob` (exécuté toutes les heures)

### Protection 48h

Si tournoi plein mais non démarré après 48h:
- Participants remboursés automatiquement
- Tournoi marqué `cancelled`
- Emails de remboursement envoyés

**Job:** `CheckFullTournamentsJob` (exécuté toutes les heures)

---

## Emails automatiques

| Événement | Email | Déclencheur |
|-----------|-------|-------------|
| Inscription | `TournamentRegistrationConfirmationMail` | POST /tournaments/{id}/register |
| Démarrage | `TournamentStartedMail` | Tournoi démarré (avec info adversaire) |
| Remboursement | `TournamentRefundMail` | CheckFullTournamentsJob (48h) |
| Message chat | `MatchMessageNotification` | POST /matches/{id}/messages |

---

## Résumé des permissions

| Action | Participant | Organisateur | Admin |
|--------|------------|--------------|-------|
| Créer tournoi | ❌ | ✅ | ✅ |
| S'inscrire | ✅ | ✅ | ✅ |
| Voir tournoi | ✅ | ✅ | ✅ |
| Démarrer tournoi | ❌ | ✅ (son tournoi) | ✅ |
| Envoyer message | ✅ (son match) | ❌ | ❌ |
| Voir messages | ✅ (son match) | ✅ (ses tournois) | ✅ |
| Upload preuve | ✅ (son match) | ❌ | ❌ |
| Voir preuves | ✅ (son match) | ✅ (ses tournois) | ✅ |
| Entrer scores | ❌ | ✅ (ses tournois) | ✅ |

---

## Notes importantes pour le Frontend

1. **Polling vs WebSockets**: Pour le chat en temps réel, considérer polling GET /matches/{id}/messages toutes les 5-10 secondes ou implémenter WebSockets

2. **Storage URL**: Les fichiers uploadés sont accessibles via:
   ```
   {APP_URL}/storage/{file_path}
   Exemple: http://localhost:8000/storage/evidence/matches/1/abc123.jpg
   ```

3. **Dates**: Toutes les dates sont au format ISO 8601 UTC. Convertir en timezone locale côté frontend

4. **Statuts**: Afficher les badges appropriés selon le statut du tournoi:
   - `open` → Badge vert "Inscriptions ouvertes"
   - `in_progress` → Badge bleu "En cours"
   - `completed` → Badge gris "Terminé"

5. **Auto-refresh**: Sur la page d'un tournoi `open`, rafraîchir périodiquement pour voir les nouvelles inscriptions et si `full_since` est rempli

6. **Wallet**: Toujours vérifier le solde avant d'afficher le bouton "S'inscrire" au tournoi

---

**Dernière mise à jour:** 21 Décembre 2025
**Version API:** 1.0
