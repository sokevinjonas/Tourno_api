# Documentation Système de Tournois - API Backend

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Workflow de création de tournoi](#workflow-de-création-de-tournoi)
3. [Statuts des tournois](#statuts-des-tournois)
4. [Système de calendrier et programmation](#système-de-calendrier-et-programmation)
5. [Endpoints API](#endpoints-api)
6. [Système de solde bloqué](#système-de-solde-bloqué)
7. [Auto-Management](#auto-management)
8. [Chat et Preuves](#chat-et-preuves)
9. [Emails automatiques](#emails-automatiques)

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

## Système de calendrier et programmation

Le système de calendrier permet une programmation automatique et intelligente des matches en fonction de la durée du tournoi, du format et du créneau horaire choisi.

### Concepts clés

#### 1. Durée du tournoi
L'organisateur définit la durée totale du tournoi en jours. Le système recommande automatiquement une durée optimale basée sur:
- **Format du tournoi** (Coupe, Suisse, Ligue des Champions)
- **Nombre de participants** (8, 16, 32 ou 64)

#### 2. Créneaux horaires disponibles

| Créneau | Heures | Description |
|---------|--------|-------------|
| `morning` | 9h - 12h | Matinée (3 heures) |
| `afternoon` | 13h - 16h | Après-midi (3 heures) |
| `evening` | 18h - 23h | Soirée (5 heures) |

#### 3. Deadline des matches
Chaque match a une deadline stricte (configurable, par défaut 60 minutes):
- Match programmé à 14h00 → Deadline à 15h00
- Si aucun résultat n'est soumis avant la deadline → Match marqué comme `expired`
- Les deux joueurs perdent (aucun gagnant)

#### 4. Formats supportés et calculs

##### Format Coupe (Single Elimination)
- **Participants**: 8, 16, 32 ou 64
- **Nombre de tours**: log₂(participants)
  - 8 joueurs → 3 tours (8→4→2→1)
  - 16 joueurs → 4 tours (16→8→4→2→1)
  - 32 joueurs → 5 tours
  - 64 joueurs → 6 tours
- **Durée recommandée**: 1 jour par tour minimum
  - 8 joueurs → 3 jours
  - 16 joueurs → 4 jours
  - 32 joueurs → 5 jours
  - 64 joueurs → 6 jours

##### Format Suisse (Swiss System)
- **Participants**: 8, 16, 32 ou 64
- **Nombre de tours**: log₂(participants)
- **Particularité**: Tous les joueurs jouent à chaque tour (pas d'élimination)
- **Durée recommandée**: 2 jours par tour
  - 8 joueurs → 6 jours (3 tours × 2 jours)
  - 16 joueurs → 8 jours (4 tours × 2 jours)
  - 32 joueurs → 10 jours
  - 64 joueurs → 12 jours

##### Format Ligue des Champions
- **Participants**: Fixe à 16 joueurs
- **Phase de groupes**: 4 groupes de 4 joueurs (3 matches par joueur)
- **Phase à élimination**: Quarts, Demi-finales, Finale
- **Nombre total de tours**: 6 (3 groupes + 3 élimination)
- **Durée recommandée**: 7 jours

### Statuts des matches

| Statut | Description |
|--------|-------------|
| `scheduled` | Match programmé, en attente |
| `in_progress` | Match en cours |
| `pending_validation` | En attente de validation par l'organisateur |
| `completed` | Match terminé avec résultat |
| `disputed` | Match contesté |
| `expired` | Deadline dépassée, aucun résultat soumis |

### Processus de programmation automatique

1. **Organisateur crée le tournoi** avec:
   - Format (coupe/suisse/champions_league)
   - Nombre de participants (8/16/32/64)
   - Date de début
   - Durée en jours (ou utilise la recommandation)
   - Créneau horaire (morning/afternoon/evening)
   - Deadline par match (minutes)

2. **Système calcule le planning**:
   - Répartit les matches uniformément sur la durée
   - Assigne des heures spécifiques dans le créneau choisi
   - Calcule automatiquement les deadlines

3. **Prévisualisation disponible** avant création du tournoi

4. **Job automatique** (`CheckMatchDeadlinesJob`):
   - S'exécute toutes les 15 minutes
   - Vérifie les matches avec deadline dépassée
   - Marque automatiquement comme `expired`
   - Aucun gagnant attribué

### Champs importants de la table `tournaments`

| Champ | Type | Description |
|-------|------|-------------|
| `format` | enum | **CRITIQUE**: Format du tournoi (single_elimination/swiss/champions_league) - Détermine l'algorithme de génération des matches |
| `tournament_duration_days` | integer | Durée totale du tournoi en jours |
| `time_slot` | enum | Créneau horaire: morning/afternoon/evening |
| `match_deadline_minutes` | integer | Délai en minutes pour soumettre le résultat (défaut: 60) |
| `total_rounds` | integer | Nombre total de tours (calculé automatiquement selon le format) |
| `current_round` | integer | Tour actuel (0 = pas encore commencé) |

**Note importante sur le champ `format`:**
Le champ `format` est essentiel et obligatoire car il détermine:
- L'algorithme utilisé pour générer les paires de matches
- La structure du tournoi (élimination directe, suisse, ou ligue des champions)
- Le nombre de tours nécessaires
- La gestion de la progression des joueurs entre les tours

### Nouveaux champs de la table `matches`

| Champ | Type | Description |
|-------|------|-------------|
| `scheduled_at` | datetime | Date et heure programmées du match |
| `deadline_at` | datetime | Date et heure limite pour soumettre le résultat |
| `status` | enum | Inclut maintenant 'expired' |

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
  "game": "fc_mobile",
  "format": "single_elimination",
  "max_participants": 16,
  "entry_fee": 5.00,
  "prize_distribution": {
    "1": 50,
    "2": 30,
    "3": 20
  },
  "visibility": "public",
  "auto_managed": true,
  "start_date": "2025-12-25 14:00:00",
  "tournament_duration_days": 4,
  "time_slot": "evening",
  "match_deadline_minutes": 60
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
    "game": "fc_mobile",
    "format": "single_elimination",
    "max_participants": 16,
    "entry_fee": "5.00",
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
    "start_date": "2025-12-25T14:00:00.000000Z",
    "actual_start_date": null,
    "tournament_duration_days": 4,
    "time_slot": "evening",
    "match_deadline_minutes": 60,
    "total_rounds": 4,
    "current_round": 0,
    "created_at": "2025-12-21T16:00:00.000000Z",
    "updated_at": "2025-12-21T16:00:00.000000Z"
  }
}
```

**Notes:**
- `format` (required): Détermine l'algorithme de génération des matches - `single_elimination`, `swiss`, ou `champions_league`
- `unique_url` sera généré automatiquement si `visibility` = "private"
- `creation_fee_paid` dépend du badge de l'organisateur (2 MLM ou 0)
- Les champs de calendrier (`tournament_duration_days`, `time_slot`, `match_deadline_minutes`) sont optionnels. Si non fournis, les valeurs par défaut sont utilisées.

---

### 2. Prévisualiser le calendrier d'un tournoi

**Endpoint:** `POST /api/tournaments/preview-schedule`
**Auth:** Not required

**Description:** Permet de prévisualiser le calendrier complet d'un tournoi avant sa création. Retourne la durée recommandée, le nombre total de tours, et le planning détaillé de tous les matches.

**Request Body:**
```json
{
  "format": "single_elimination",
  "max_participants": 16,
  "start_date": "2025-12-27 00:00:00",
  "tournament_duration_days": 4,
  "time_slot": "evening",
  "match_deadline_minutes": 60
}
```

**Paramètres:**
- `format` (required): Format du tournoi pour le calcul - `single_elimination`, `swiss`, ou `champions_league`
- `max_participants` (required): 8, 16, 32, ou 64
- `start_date` (required): Date de début du tournoi
- `tournament_duration_days` (optional): Durée en jours. Si non fourni, la durée recommandée sera utilisée
- `time_slot` (optional): `morning`, `afternoon`, ou `evening` (défaut: `evening`)
- `match_deadline_minutes` (optional): Délai en minutes (défaut: 60)

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "recommended_duration_days": 4,
    "total_rounds": 4,
    "schedule": [
      {
        "round_number": 1,
        "start_date": "2025-12-27T00:00:00.000000Z",
        "matches": [
          {
            "match_number": 1,
            "scheduled_at": "2025-12-27T18:00:00.000000Z",
            "deadline_at": "2025-12-27T19:00:00.000000Z"
          },
          {
            "match_number": 2,
            "scheduled_at": "2025-12-27T19:00:00.000000Z",
            "deadline_at": "2025-12-27T20:00:00.000000Z"
          },
          {
            "match_number": 3,
            "scheduled_at": "2025-12-27T20:00:00.000000Z",
            "deadline_at": "2025-12-27T21:00:00.000000Z"
          },
          {
            "match_number": 4,
            "scheduled_at": "2025-12-27T21:00:00.000000Z",
            "deadline_at": "2025-12-27T22:00:00.000000Z"
          },
          {
            "match_number": 5,
            "scheduled_at": "2025-12-27T22:00:00.000000Z",
            "deadline_at": "2025-12-27T23:00:00.000000Z"
          },
          {
            "match_number": 6,
            "scheduled_at": "2025-12-28T18:00:00.000000Z",
            "deadline_at": "2025-12-28T19:00:00.000000Z"
          },
          {
            "match_number": 7,
            "scheduled_at": "2025-12-28T19:00:00.000000Z",
            "deadline_at": "2025-12-28T20:00:00.000000Z"
          },
          {
            "match_number": 8,
            "scheduled_at": "2025-12-28T20:00:00.000000Z",
            "deadline_at": "2025-12-28T21:00:00.000000Z"
          }
        ]
      },
      {
        "round_number": 2,
        "start_date": "2025-12-28T00:00:00.000000Z",
        "matches": [
          {
            "match_number": 1,
            "scheduled_at": "2025-12-28T21:00:00.000000Z",
            "deadline_at": "2025-12-28T22:00:00.000000Z"
          },
          {
            "match_number": 2,
            "scheduled_at": "2025-12-28T22:00:00.000000Z",
            "deadline_at": "2025-12-28T23:00:00.000000Z"
          },
          {
            "match_number": 3,
            "scheduled_at": "2025-12-29T18:00:00.000000Z",
            "deadline_at": "2025-12-29T19:00:00.000000Z"
          },
          {
            "match_number": 4,
            "scheduled_at": "2025-12-29T19:00:00.000000Z",
            "deadline_at": "2025-12-29T20:00:00.000000Z"
          }
        ]
      },
      {
        "round_number": 3,
        "start_date": "2025-12-29T00:00:00.000000Z",
        "matches": [
          {
            "match_number": 1,
            "scheduled_at": "2025-12-29T20:00:00.000000Z",
            "deadline_at": "2025-12-29T21:00:00.000000Z"
          },
          {
            "match_number": 2,
            "scheduled_at": "2025-12-29T21:00:00.000000Z",
            "deadline_at": "2025-12-29T22:00:00.000000Z"
          }
        ]
      },
      {
        "round_number": 4,
        "start_date": "2025-12-30T00:00:00.000000Z",
        "matches": [
          {
            "match_number": 1,
            "scheduled_at": "2025-12-30T20:00:00.000000Z",
            "deadline_at": "2025-12-30T21:00:00.000000Z"
          }
        ]
      }
    ]
  }
}
```

**Cas d'utilisation:**
- Avant de créer un tournoi, l'organisateur peut voir exactement comment les matches seront répartis
- Permet de vérifier si la durée choisie est appropriée
- Affiche les recommandations du système pour optimiser le planning

---

### 3. Lister tous les tournois publics

**Endpoint:** `GET /api/tournaments`
**Auth:** Not required

**Query Parameters:**
- `status` (optional): `open`, `in_progress`, `completed`
- `game` (optional): `efootball`, `fc_mobile`, `dream_league_soccer`
- `sort` (optional): `asc`, `desc` (default: `desc`)

**Response:** `200 OK`
```json
{
  "tournaments": [
    {
      "id": 1,
      "name": "Championship FIFA Mobile 2025",
      "game": "fc_mobile",
      "format": "single_elimination",
      "max_participants": 16,
      "entry_fee": "5.00",
      "status": "open",
      "visibility": "public",
      "start_date": "2025-12-25T14:00:00.000000Z",
      "tournament_duration_days": 4,
      "time_slot": "evening",
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
  "game": "fc_mobile",
  "format": "single_elimination",
  "max_participants": 16,
  "entry_fee": "5.00",
  "prize_distribution": {
    "1": 50,
    "2": 30,
    "3": 20
  },
  "status": "open",
  "visibility": "public",
  "start_date": "2025-12-25T14:00:00.000000Z",
  "tournament_duration_days": 4,
  "time_slot": "evening",
  "match_deadline_minutes": 60,
  "total_rounds": 4,
  "current_round": 0,
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
}
```

**Note:** Les paramètres supplémentaires seront ajoutés selon les besoins (ex: game_account_id si le système de comptes de jeu est implémenté)

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Successfully registered for tournament",
  "data": {
    "id": 1,
    "tournament_id": 1,
    "user_id": 10,
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
        "game": "fc_mobile",
        "format": "single_elimination",
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

### Gestion automatique des deadlines de matches

Le système vérifie automatiquement les matches dont la deadline est dépassée:
- Matches avec `deadline_at` < maintenant
- Statut non finalisé (`scheduled`, `in_progress`)
- Automatiquement marqués comme `expired`
- Aucun gagnant attribué (`winner_id = null`)
- Scores remis à null
- `completed_at` enregistré

**Job:** `CheckMatchDeadlinesJob` (exécuté toutes les 15 minutes)

**Impact:**
- Les deux joueurs perdent le match
- Le tour ne peut progresser que si tous les autres matches sont terminés
- L'organisateur peut intervenir manuellement pour résoudre le problème

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

4. **Statuts des tournois**: Afficher les badges appropriés selon le statut du tournoi:
   - `open` → Badge vert "Inscriptions ouvertes"
   - `in_progress` → Badge bleu "En cours"
   - `completed` → Badge gris "Terminé"

5. **Statuts des matches**: Afficher visuellement l'état de chaque match:
   - `scheduled` → Badge gris "Programmé" + afficher `scheduled_at` et `deadline_at`
   - `in_progress` → Badge bleu "En cours"
   - `pending_validation` → Badge orange "En attente"
   - `completed` → Badge vert "Terminé"
   - `disputed` → Badge rouge "Contesté"
   - `expired` → Badge rouge foncé "Expiré" (deadline dépassée, aucun gagnant)

6. **Prévisualisation du calendrier**: Utiliser `POST /api/tournaments/preview-schedule` pour:
   - Afficher le calendrier complet avant la création du tournoi
   - Montrer la durée recommandée selon le format et le nombre de participants
   - Permettre à l'organisateur d'ajuster la durée et le créneau horaire
   - Valider que le planning proposé convient avant de créer le tournoi

7. **Créneaux horaires**: Afficher clairement les plages horaires:
   - Morning (Matin): 9h - 12h
   - Afternoon (Après-midi): 13h - 16h
   - Evening (Soirée): 18h - 23h

8. **Deadline countdown**: Pour les matches programmés:
   - Afficher un compte à rebours jusqu'à la deadline
   - Alerte visuelle quand il reste moins de 30 minutes
   - Notification push recommandée à 15 minutes de la deadline
   - Après expiration, afficher "Match expiré" avec explications

9. **Auto-refresh**:
   - Sur la page d'un tournoi `open`, rafraîchir périodiquement pour voir les nouvelles inscriptions
   - Sur la page d'un match en cours, rafraîchir pour voir les mises à jour de statut
   - Vérifier périodiquement si des matches ont expiré

10. **Wallet**: Toujours vérifier le solde avant d'afficher le bouton "S'inscrire" au tournoi

11. **Calendrier visuel**: Considérer l'affichage d'un calendrier visuel montrant:
    - La répartition des matches sur la durée du tournoi
    - Les tours avec leurs dates respectives
    - Les matches programmés avec leurs horaires
    - Les deadlines clairement indiquées

---

**Dernière mise à jour:** 21 Décembre 2025
**Version API:** 1.1 (Système de calendrier et programmation automatique ajouté)
