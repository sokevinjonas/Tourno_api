# 🏗️ Architecture Technique & Diagrammes - MLM API

**Version** : 1.0
**Date** : Décembre 2024

---

## Table des Matières

1. [Diagrammes d'Architecture](#1-diagrammes-darchitecture)
2. [Diagrammes de Séquence](#2-diagrammes-de-séquence)
3. [Diagramme de Classes](#3-diagramme-de-classes)
4. [Spécifications API Complètes](#4-spécifications-api-complètes)
5. [Schéma de Base de Données](#5-schéma-de-base-de-données)
6. [Intégrations Externes](#6-intégrations-externes)

---

## 1. Diagrammes d'Architecture

### 1.1 Architecture Globale

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐             │
│  │  Ionic App   │  │  Web Portal  │  │  Admin Panel │             │
│  │  (Mobile)    │  │  (Browser)   │  │  (Browser)   │             │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘             │
│         │                  │                  │                      │
│         └──────────────────┼──────────────────┘                      │
│                            │                                         │
└────────────────────────────┼─────────────────────────────────────────┘
                             │ HTTPS (REST API + WebSockets)
┌────────────────────────────▼─────────────────────────────────────────┐
│                         API LAYER (Laravel)                          │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  API Gateway (Sanctum Auth + Rate Limiting)                 │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐             │
│  │  Tournament  │  │   Wallet     │  │  Division    │             │
│  │  Service     │  │   Service    │  │  Service     │             │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘             │
│         │                  │                  │                      │
│  ┌──────▼──────────────────▼──────────────────▼───────┐            │
│  │         Business Logic Layer                        │            │
│  │  • BracketGenerator  • EloCalculator                │            │
│  │  • ScoreValidator    • PaymentProcessor             │            │
│  │  • DisputeManager    • PromotionManager             │            │
│  └─────────────────────────┬───────────────────────────┘            │
│                            │                                         │
└────────────────────────────┼─────────────────────────────────────────┘
                             │ Eloquent ORM
┌────────────────────────────▼─────────────────────────────────────────┐
│                      DATA LAYER                                      │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐        │
│  │   MySQL/       │  │     Redis      │  │   File Storage │        │
│  │   PostgreSQL   │  │   (Cache +     │  │   (S3/Local)   │        │
│  │                │  │    Queue)      │  │                │        │
│  └────────────────┘  └────────────────┘  └────────────────┘        │
└──────────────────────────────────────────────────────────────────────┘
                             │
                             │ HTTPS (Webhooks)
┌────────────────────────────▼─────────────────────────────────────────┐
│                   EXTERNAL SERVICES                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐             │
│  │  Payment     │  │  Firebase    │  │  Email/SMS   │             │
│  │  Gateway     │  │  (FCM Push)  │  │  Provider    │             │
│  │  (CinetPay)  │  │              │  │  (Twilio)    │             │
│  └──────────────┘  └──────────────┘  └──────────────┘             │
└──────────────────────────────────────────────────────────────────────┘
```

---

### 1.2 Architecture des Services

```
┌──────────────────────────────────────────────────────────────┐
│                    TOURNAMENT SERVICE                        │
│  • createTournament()      • generateBracket()               │
│  • startTournament()       • validateScore()                 │
│  • finalizeTournament()    • handleDispute()                 │
│  • refundParticipants()                                      │
└────────────────┬─────────────────────────────────────────────┘
                 │
                 ├──► BracketGenerator
                 ├──► ScoreValidator
                 ├──► DisputeManager
                 └──► EloCalculator

┌──────────────────────────────────────────────────────────────┐
│                     WALLET SERVICE                           │
│  • deposit()               • withdraw()                      │
│  • transfer()              • getBalance()                    │
│  • getTransactions()       • processWithdrawal()             │
└────────────────┬─────────────────────────────────────────────┘
                 │
                 ├──► PaymentGateway (CinetPay, FedaPay)
                 ├──► TransactionLogger
                 └──► BalanceManager

┌──────────────────────────────────────────────────────────────┐
│                    DIVISION SERVICE                          │
│  • joinDivision()          • generateTournaments()           │
│  • promotePlayer()         • relegatePlayer()                │
│  • processSeasonEnd()      • calculateStandings()            │
└────────────────┬─────────────────────────────────────────────┘
                 │
                 ├──► PromotionManager
                 ├──► SeasonManager
                 └──► TournamentScheduler (Cron)
```

---

## 2. Diagrammes de Séquence

### 2.1 Inscription et Participation à un Tournoi Payant

```
Joueur          App Mobile      API Laravel      Wallet Service    Tournament Service    Payment Gateway
  │                 │                │                 │                   │                    │
  │─────Browse──────▶               │                 │                   │                    │
  │                 │──GET /tournaments───────────────▶                   │                    │
  │                 │◀────Tournois disponibles────────│                   │                    │
  │                 │                │                 │                   │                    │
  │──Clique "S'inscrire"───────────▶                 │                   │                    │
  │                 │──POST /tournaments/{id}/join────▶                   │                    │
  │                 │                │─────Check Entry Fee───────────────▶                    │
  │                 │                │◀────Montant requis: 20 coins──────│                    │
  │                 │                │                 │                   │                    │
  │                 │                │──Verify Balance─▶                  │                    │
  │                 │                │◀───Balance OK───│                  │                    │
  │                 │                │                 │                   │                    │
  │                 │                │──Debit Wallet───▶                  │                    │
  │                 │                │ (Create Transaction)                │                    │
  │                 │                │◀───Transaction OK──                │                    │
  │                 │                │                 │                   │                    │
  │                 │                │──────Create Participant────────────▶                    │
  │                 │                │◀─────Participant Created───────────│                    │
  │                 │                │                 │                   │                    │
  │                 │◀───✅ Inscription réussie───────│                  │                    │
  │◀──Notification──│                │                 │                   │                    │
  │  "Inscrit!"     │                │                 │                   │                    │
  │                 │                │                 │                   │                    │
  │─────[Tournoi démarre]──────────────────────────────────────────────────────────────────────│
  │                 │                │                 │                   │                    │
  │                 │                │───Generate Bracket─────────────────▶                    │
  │                 │                │◀───Bracket Ready──────────────────│                    │
  │◀──Push Notif───│                │                 │                   │                    │
  │  "Match Ready"  │                │                 │                   │                    │
```

---

### 2.2 Déclaration de Score et Validation Automatique

```
Joueur A        Joueur B        API Laravel      ScoreValidator    Match Service    Notification Service
  │                 │                │                 │                 │                  │
  │──Déclare Score──────────────────▶               │                 │                  │
  │  (3-1 + proof) │                │                 │                 │                  │
  │                 │                │──Create Declaration──────────────▶                  │
  │                 │                │─────Notify B─────────────────────────────────────────▶
  │                 │◀───Push: "A a déclaré"──────────────────────────────────────────────│
  │                 │                │                 │                 │                  │
  │                 │──Déclare Score──────────────────▶                │                  │
  │                 │  (3-1 + proof) │                 │                 │                  │
  │                 │                │──Create Declaration──────────────▶                  │
  │                 │                │                 │                 │                  │
  │                 │                │──Compare Scores─▶                │                  │
  │                 │                │                 │                 │                  │
  │                 │                │◀───Scores Match──                │                  │
  │                 │                │                 │                 │                  │
  │                 │                │──Update Match Status──────────────▶                  │
  │                 │                │  (completed, winner: A)            │                  │
  │                 │                │                 │                 │                  │
  │                 │                │──Promote Winner─▶                │                  │
  │                 │                │                 │                 │                  │
  │                 │                │─────Notify Both─────────────────────────────────────▶
  │◀───✅ Match validé──────────────────────────────────────────────────────────────────────│
  │◀───"Qualifié!"──│                │                 │                 │                  │
  │                 │◀───"Éliminé"───────────────────────────────────────────────────────────│
```

---

### 2.3 Recharge de Solde (Mobile Money)

```
Joueur          App Mobile      API Laravel      Wallet Service    Payment Gateway    Mobile Money
  │                 │                │                 │                  │                 │
  │──Clique "Recharger"─────────────▶               │                  │                 │
  │  Montant: 100 coins              │                 │                  │                 │
  │                 │──POST /wallet/deposit──────────▶                  │                 │
  │                 │  {amount: 100, method: "orange"}│                  │                 │
  │                 │                │                 │                  │                 │
  │                 │                │──Create Transaction─────────────▶                  │
  │                 │                │  (status: pending)                │                  │
  │                 │                │                 │                  │                 │
  │                 │                │──Initiate Payment─────────────────▶                 │
  │                 │                │                 │──API Call────────▶                 │
  │                 │                │                 │  (1000 FCFA)     │                 │
  │                 │                │                 │◀──Payment URL────│                 │
  │                 │                │◀───Payment URL──│                  │                 │
  │                 │◀───Redirect to Gateway──────────│                  │                 │
  │                 │                │                 │                  │                 │
  │──────[User paye via Orange Money]───────────────────────────────────────────────────────▶
  │                 │                │                 │                  │◀──USSD/Confirm──│
  │                 │                │                 │                  │                 │
  │                 │                │                 │◀───Webhook: Success───────────────│
  │                 │                │◀──Callback (success)──────────────│                 │
  │                 │                │                 │                  │                 │
  │                 │                │──Credit Balance─▶                  │                 │
  │                 │                │  (Update Transaction)               │                 │
  │                 │                │◀───Balance Updated                │                 │
  │                 │                │                 │                  │                 │
  │                 │◀───✅ Recharge réussie──────────│                  │                 │
  │◀──Push Notif───│                │                 │                  │                 │
  │  "+100 coins"   │                │                 │                 │                 │
```

---

### 2.4 Fin de Tournoi et Distribution des Gains

```
System Cron     API Laravel    Tournament Service   Wallet Service   Notification Service
  │                 │                 │                  │                    │
  │──Check Tournaments──────────────▶                  │                    │
  │  (Finale completed?)              │                  │                    │
  │                 │──Get Tournament─▶                 │                    │
  │                 │◀───Tournament───│                  │                    │
  │                 │                 │                  │                    │
  │                 │──Finalize Tournament──────────────▶                    │
  │                 │                 │                  │                    │
  │                 │                 │──Calculate Final Positions──────────│
  │                 │                 │                  │                    │
  │                 │                 │──Distribute Prizes────────────────▶ │
  │                 │                 │  Winner: 68 coins                    │
  │                 │                 │  2nd: 40.8 coins                     │
  │                 │                 │  3-4: 13.6 coins each                │
  │                 │                 │                  │                    │
  │                 │                 │──For Each Winner────────────────────▶
  │                 │                 │                  │                    │
  │                 │                 │──Credit Wallet───▶                  │
  │                 │                 │  (Create Transaction)                │
  │                 │                 │◀───Credit OK─────│                  │
  │                 │                 │                  │                    │
  │                 │                 │──Notify Winner─────────────────────▶
  │                 │                 │                  │                    │
  │                 │                 │──Update ELO Ranks──────────────────▶
  │                 │                 │                  │                    │
  │                 │                 │──Mark Tournament Complete───────────│
  │                 │                 │  (status: completed)                 │
  │                 │                 │                  │                    │
  │                 │◀───All Winners Paid──────────────│                    │
```

---

## 3. Diagramme de Classes

### 3.1 Core Models

```
┌─────────────────────────────────┐
│           User                  │
├─────────────────────────────────┤
│ + id                            │
│ + username                      │
│ + email                         │
│ + wallet_balance                │
│ + mlm_rank                      │
│ + current_division_id           │
├─────────────────────────────────┤
│ + tournaments()                 │
│ + participants()                │
│ + transactions()                │
│ + withdrawals()                 │
│ + division()                    │
└───────────┬─────────────────────┘
            │ 1
            │ organizes
            │ *
┌───────────▼─────────────────────┐
│        Tournament               │
├─────────────────────────────────┤
│ + id                            │
│ + organizer_id                  │
│ + game_id                       │
│ + division_id                   │
│ + is_paid                       │
│ + entry_fee                     │
│ + prize_pool                    │
│ + prize_distribution (JSON)     │
├─────────────────────────────────┤
│ + organizer()                   │
│ + participants()                │
│ + rounds()                      │
│ + matches()                     │
│ + division()                    │
│ + generateBracket()             │
│ + finalize()                    │
└───────────┬─────────────────────┘
            │ 1
            │ has
            │ *
┌───────────▼─────────────────────┐
│    TournamentParticipant        │
├─────────────────────────────────┤
│ + id                            │
│ + tournament_id                 │
│ + user_id                       │
│ + seed                          │
│ + final_position                │
│ + elo_before                    │
│ + elo_after                     │
├─────────────────────────────────┤
│ + user()                        │
│ + tournament()                  │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│        WalletTransaction        │
├─────────────────────────────────┤
│ + id                            │
│ + user_id                       │
│ + type (enum)                   │
│ + amount                        │
│ + balance_before                │
│ + balance_after                 │
│ + status                        │
│ + tournament_id                 │
├─────────────────────────────────┤
│ + user()                        │
│ + tournament()                  │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│          Division               │
├─────────────────────────────────┤
│ + id                            │
│ + game_id                       │
│ + name                          │
│ + level                         │
│ + entry_fee                     │
│ + min_mlm_rank                  │
│ + max_mlm_rank                  │
│ + tournament_frequency          │
├─────────────────────────────────┤
│ + game()                        │
│ + memberships()                 │
│ + tournaments()                 │
│ + generateTournament()          │
└─────────────────────────────────┘
```

---

## 4. Spécifications API Complètes

### 4.1 Authentification

#### POST /api/register

**Description** : Créer un nouveau compte utilisateur

**Request Body** :

```json
{
  "username": "karim_dls",
  "email": "karim@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "phone_number": "+221771234567"
}
```

**Response 201** :

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "username": "karim_dls",
      "email": "karim@example.com",
      "mlm_rank": 1000,
      "wallet_balance": 0,
      "avatar_url": null
    },
    "token": "eyJ0eXAiOiJKV1QiLC..."
  },
  "message": "Compte créé avec succès"
}
```

---

#### POST /api/login

**Request Body** :

```json
{
  "email": "karim@example.com",
  "password": "SecurePass123!"
}
```

**Response 200** :

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "username": "karim_dls",
      "email": "karim@example.com",
      "mlm_rank": 1000,
      "wallet_balance": 50.00,
      "current_division_id": 3
    },
    "token": "eyJ0eXAiOiJKV1QiLC..."
  }
}
```

---

### 4.2 Wallet (Porte-monnaie)

#### POST /api/wallet/deposit

**Auth** : Required
**Description** : Initier une recharge de solde

**Request Body** :

```json
{
  "amount": 100,
  "payment_method": "orange_money"
}
```

**Response 200** :

```json
{
  "success": true,
  "data": {
    "transaction_id": 456,
    "payment_url": "https://payment.cinetpay.com/payment/xyz123",
    "amount": 100,
    "amount_fcfa": 1000,
    "status": "pending"
  },
  "message": "Redirection vers le paiement"
}
```

---

#### POST /api/wallet/withdraw

**Auth** : Required
**Description** : Demander un retrait de fonds

**Request Body** :

```json
{
  "amount": 50,
  "phone_number": "+221771234567",
  "payment_method": "orange_money"
}
```

**Response 200** :

```json
{
  "success": true,
  "data": {
    "withdrawal_request_id": 789,
    "amount": 50,
    "amount_fcfa": 500,
    "status": "pending",
    "estimated_processing_time": "1-24 heures"
  },
  "message": "Demande de retrait créée. En attente de traitement."
}
```

---

#### GET /api/wallet/transactions

**Auth** : Required
**Query Params** : `?page=1&per_page=20&type=deposit`

**Response 200** :

```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 101,
        "type": "deposit",
        "amount": 100,
        "balance_before": 50,
        "balance_after": 150,
        "status": "completed",
        "description": "Recharge de 100 coins",
        "created_at": "2024-12-03T10:30:00Z"
      },
      {
        "id": 102,
        "type": "tournament_entry",
        "amount": -20,
        "balance_before": 150,
        "balance_after": 130,
        "status": "completed",
        "tournament_id": 55,
        "description": "Inscription au tournoi: Weekend Battle",
        "created_at": "2024-12-03T11:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "per_page": 20,
      "total": 95
    }
  }
}
```

---

### 4.3 Tournois

#### GET /api/tournaments

**Query Params** :

- `game_id` (optional)
- `type` (knockout|league)
- `status` (registration|ongoing|completed)
- `is_paid` (true|false)
- `page`, `per_page`

**Response 200** :

```json
{
  "success": true,
  "data": {
    "tournaments": [
      {
        "id": 55,
        "name": "Weekend Battle",
        "game": {
          "id": 2,
          "name": "E-football 2024",
          "icon_url": "https://..."
        },
        "type": "knockout",
        "format": "8",
        "status": "registration",
        "current_players_count": 5,
        "max_players": 8,
        "is_paid": true,
        "entry_fee": 20,
        "prize_pool": 136,
        "organizer": {
          "id": 10,
          "username": "Amadou"
        },
        "registration_deadline": "2024-12-05T15:00:00Z",
        "is_public": true
      }
    ],
    "pagination": {...}
  }
}
```

---

#### POST /api/tournaments

**Auth** : Required
**Description** : Créer un nouveau tournoi

**Request Body** :

```json
{
  "name": "Weekend Battle",
  "description": "Tournoi week-end pour le clan",
  "game_id": 2,
  "type": "knockout",
  "format": "8",
  "max_players": 8,
  "is_paid": true,
  "entry_fee": 20,
  "prize_distribution": {
    "1": 50,
    "2": 30,
    "3-4": 10
  },
  "match_deadline_hours": 24,
  "is_public": true,
  "registration_deadline": "2024-12-05T15:00:00Z"
}
```

**Response 201** :

```json
{
  "success": true,
  "data": {
    "tournament": {
      "id": 55,
      "name": "Weekend Battle",
      "status": "registration",
      "invitation_code": "WB-2024-55",
      "invitation_link": "https://mlm.app/tournaments/55",
      "prize_pool": 136,
      "platform_fee": 16,
      "organizer_fee": 8
    }
  },
  "message": "Tournoi créé avec succès"
}
```

---

#### POST /api/tournaments/{id}/join

**Auth** : Required
**Description** : S'inscrire à un tournoi

**Response 200** :

```json
{
  "success": true,
  "data": {
    "participant": {
      "id": 200,
      "user_id": 123,
      "tournament_id": 55,
      "seed": null,
      "elo_before": 1000
    },
    "tournament": {
      "current_players_count": 6,
      "max_players": 8
    },
    "transaction": {
      "id": 105,
      "amount": -20,
      "new_balance": 110
    }
  },
  "message": "Inscription réussie. Solde débité : 20 coins"
}
```

**Response 400** (solde insuffisant) :

```json
{
  "success": false,
  "error": {
    "code": "INSUFFICIENT_BALANCE",
    "message": "Solde insuffisant. Requis: 20 coins, Disponible: 10 coins"
  }
}
```

---

#### POST /api/tournaments/{id}/start

**Auth** : Required (Organisateur uniquement)
**Description** : Démarrer le tournoi et générer le bracket

**Response 200** :

```json
{
  "success": true,
  "data": {
    "tournament": {
      "id": 55,
      "status": "ongoing",
      "started_at": "2024-12-05T15:05:00Z"
    },
    "bracket": {
      "rounds": [
        {
          "round_number": 1,
          "name": "Quarts de Finale",
          "matches": [
            {
              "match_number": 1,
              "player1": {"id": 123, "username": "Amadou", "seed": 1},
              "player2": {"id": 456, "username": "Youssef", "seed": 8}
            },
            ...
          ]
        }
      ]
    }
  },
  "message": "Tournoi démarré ! Bracket généré."
}
```

---

#### GET /api/tournaments/{id}/bracket

**Description** : Obtenir le bracket complet

**Response 200** :

```json
{
  "success": true,
  "data": {
    "rounds": [
      {
        "round_number": 1,
        "name": "Quarts de Finale",
        "matches": [
          {
            "id": 301,
            "match_number": 1,
            "player1": {"id": 10, "username": "Amadou"},
            "player2": {"id": 20, "username": "Youssef"},
            "status": "completed",
            "player1_score": 3,
            "player2_score": 1,
            "winner_id": 10
          }
        ]
      },
      {
        "round_number": 2,
        "name": "Demi-Finales",
        "matches": [
          {
            "id": 305,
            "match_number": 5,
            "player1": {"id": 10, "username": "Amadou"},
            "player2": null,
            "status": "pending"
          }
        ]
      }
    ]
  }
}
```

---

### 4.4 Matchs

#### POST /api/matches/{id}/declare-score

**Auth** : Required
**Description** : Déclarer le score d'un match

**Request Body** (multipart/form-data) :

```json
{
  "player1_score": 3,
  "player2_score": 1,
  "proof": <file>
}
```

**Response 200** (en attente de l'autre joueur) :

```json
{
  "success": true,
  "data": {
    "score_declaration": {
      "id": 401,
      "match_id": 301,
      "user_id": 123,
      "player1_score": 3,
      "player2_score": 1,
      "proof_url": "https://storage.mlm.app/proofs/xyz.jpg"
    },
    "match_status": "awaiting_results"
  },
  "message": "Score déclaré. En attente de la confirmation de votre adversaire."
}
```

**Response 200** (validation automatique) :

```json
{
  "success": true,
  "data": {
    "match": {
      "id": 301,
      "status": "completed",
      "player1_score": 3,
      "player2_score": 1,
      "winner_id": 10
    },
    "next_match": {
      "id": 305,
      "round_number": 2,
      "opponent": {"id": 30, "username": "Sarah"}
    }
  },
  "message": "✅ Match validé ! Vous êtes qualifié pour les demi-finales."
}
```

**Response 200** (litige) :

```json
{
  "success": true,
  "data": {
    "match": {
      "id": 301,
      "status": "disputed"
    },
    "dispute": {
      "id": 501,
      "status": "pending"
    }
  },
  "message": "⚠️ Litige détecté. L'organisateur va examiner les preuves."
}
```

---

### 4.5 Divisions

#### GET /api/divisions

**Query Params** : `game_id` (optional)

**Response 200** :

```json
{
  "success": true,
  "data": {
    "divisions": [
      {
        "id": 1,
        "name": "Ligue 1",
        "level": 1,
        "description": "Division Élite",
        "entry_fee": 50,
        "min_mlm_rank": 1500,
        "max_mlm_rank": null,
        "current_members_count": 45,
        "max_members": 100,
        "tournament_frequency": "weekly",
        "tournament_format": "knockout",
        "tournament_size": 16
      },
      {
        "id": 2,
        "name": "Ligue 2",
        "level": 2,
        "entry_fee": 30,
        "min_mlm_rank": 1200,
        "max_mlm_rank": 1499,
        "current_members_count": 78,
        "max_members": 150
      }
    ]
  }
}
```

---

#### POST /api/divisions/{id}/join

**Auth** : Required
**Description** : Rejoindre une division

**Response 200** :

```json
{
  "success": true,
  "data": {
    "membership": {
      "id": 600,
      "user_id": 123,
      "division_id": 2,
      "status": "active",
      "season_points": 0,
      "rank_in_division": null
    },
    "transaction": {
      "id": 110,
      "amount": -30,
      "new_balance": 80
    }
  },
  "message": "Bienvenue dans la Ligue 2 ! Votre premier tournoi démarre lundi."
}
```

**Response 400** (MLM Rank insuffisant) :

```json
{
  "success": false,
  "error": {
    "code": "RANK_NOT_ELIGIBLE",
    "message": "Votre MLM Rank (1050) ne correspond pas à cette division (1200-1499)"
  }
}
```

---

#### GET /api/divisions/{id}/standings

**Description** : Classement de la division

**Response 200** :

```json
{
  "success": true,
  "data": {
    "season": "Saison 1 - Décembre 2024",
    "standings": [
      {
        "rank": 1,
        "user": {
          "id": 50,
          "username": "Sarah",
          "avatar_url": "https://..."
        },
        "season_points": 145,
        "season_wins": 12,
        "season_losses": 2,
        "mlm_rank": 1450
      },
      {
        "rank": 2,
        "user": {
          "id": 10,
          "username": "Amadou"
        },
        "season_points": 120,
        "season_wins": 10,
        "season_losses": 3,
        "mlm_rank": 1420
      }
    ],
    "promotion_zone": 5,
    "relegation_zone": 5
  }
}
```

---

## 5. Schéma de Base de Données

### 5.1 Schéma ERD (Entity-Relationship Diagram)

```
                    ┌──────────────┐
                    │    Games     │
                    └──────┬───────┘
                           │ 1
           ┌───────────────┼───────────────┐
           │ *             │ *             │
    ┌──────▼──────┐ ┌──────▼──────┐      │
    │ Tournaments │ │  Divisions  │      │
    └──────┬──────┘ └──────┬──────┘      │
           │ 1             │ 1            │
           │ *             │ *            │
    ┌──────▼──────────────▼────────┐     │
    │  Tournament_Participants     │     │
    │  Division_Memberships        │     │
    └──────┬───────────────────────┘     │
           │ N                            │
           │                              │
    ┌──────▼──────┐              ┌───────▼──────┐
    │    Users    │◀─────────────│  Matches     │
    └──────┬──────┘     *    1   └──────┬───────┘
           │                            │ 1
           │ 1                          │ *
           │ *                   ┌──────▼────────────┐
    ┌──────▼──────────────┐     │ Score_Declarations│
    │ Wallet_Transactions │     │    Disputes       │
    │ Withdrawal_Requests │     └───────────────────┘
    │    Notifications    │
    └─────────────────────┘
```

---

### 5.2 Index et Performances

**Index recommandés** :

```sql
-- Users
CREATE INDEX idx_users_mlm_rank ON users(mlm_rank);
CREATE INDEX idx_users_division ON users(current_division_id);

-- Tournaments
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_tournaments_game ON tournaments(game_id);
CREATE INDEX idx_tournaments_division ON tournaments(division_id);
CREATE INDEX idx_tournaments_dates ON tournaments(registration_deadline, started_at);

-- Matches
CREATE INDEX idx_matches_tournament ON matches(tournament_id);
CREATE INDEX idx_matches_players ON matches(player1_id, player2_id);
CREATE INDEX idx_matches_status ON matches(status);

-- Wallet Transactions
CREATE INDEX idx_transactions_user ON wallet_transactions(user_id);
CREATE INDEX idx_transactions_type ON wallet_transactions(type);
CREATE INDEX idx_transactions_created ON wallet_transactions(created_at);

-- Divisions
CREATE INDEX idx_divisions_level ON divisions(level);
CREATE INDEX idx_divisions_rank_range ON divisions(min_mlm_rank, max_mlm_rank);
```

---

## 6. Intégrations Externes

### 6.1 Payment Gateway (CinetPay / FedaPay)

**Endpoints utilisés** :

- **Initiate Payment** : `POST /v2/payment`
- **Check Status** : `GET /v2/payment/{transaction_id}`
- **Webhook** : `POST /api/payment/callback` (notre endpoint)

**Flow** :

1. Joueur demande recharge → API MLM initie paiement
2. Gateway retourne `payment_url`
3. Joueur paie via mobile money
4. Gateway envoie webhook à MLM
5. MLM crédite le compte

---

### 6.2 Firebase Cloud Messaging (FCM)

**Usage** : Notifications push vers les apps mobiles

**Payload Type** :

```json
{
  "to": "/topics/user_123",
  "notification": {
    "title": "Match prêt !",
    "body": "Votre adversaire vous attend : Sarah",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  },
  "data": {
    "type": "match_ready",
    "match_id": "301",
    "tournament_id": "55"
  }
}
```

---

### 6.3 File Storage (AWS S3 / Local)

**Usage** : Stockage des captures d'écran de preuves

**Structure** :

```
s3://mlm-storage/
  ├── proofs/
  │   ├── 2024/12/
  │   │   ├── match_301_user_123.jpg
  │   │   └── match_301_user_456.jpg
  └── avatars/
      └── user_123.jpg
```

---

## 7. Sécurité

### 7.1 Authentification

- **Sanctum** : Token-based authentication
- Token expiration : 30 jours (configurable)
- Refresh token : Non (stateless)

### 7.2 Rate Limiting

```
Public endpoints : 60 req/min
Authenticated : 120 req/min
Wallet operations : 10 req/min
```

### 7.3 Validation des Données

- **Sanitization** : Strip tags, trim
- **XSS Protection** : Escape output
- **CSRF Protection** : Laravel built-in
- **SQL Injection** : Eloquent ORM (prepared statements)

---

## 8. Monitoring & Logging

### 8.1 Logs

```
storage/logs/
  ├── laravel.log
  ├── payment.log (transactions financières)
  ├── dispute.log (litiges)
  └── tournament.log (événements tournois)
```

### 8.2 Metrics à Surveiller

- Temps de réponse API
- Taux de réussite des paiements
- Nombre de litiges par tournoi
- Transactions wallet par jour
- Tournois actifs

---

**Document vivant** : Cette architecture évoluera avec le projet.

