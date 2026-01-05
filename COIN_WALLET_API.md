# API Coin Wallet - Guide Frontend

## 📋 Table des Matières
- [Vue d'ensemble](#vue-densemble)
- [Configuration Backend](#configuration-backend)
- [Informations générales](#informations-générales)
- [Endpoints Utilisateur](#endpoints-utilisateur)
- [Endpoints Admin/Moderator](#endpoints-adminmoderator)
- [Interface Admin à implémenter](#interface-admin-à-implémenter)
- [Gestion des erreurs](#gestion-des-erreurs)
- [Exemples de code](#exemples-de-code)

---

## Vue d'ensemble

Le système de Coin Wallet permet aux utilisateurs de:
- **Déposer des pièces** via paiement mobile money (FusionPay) - Automatique
- **Retirer des pièces** vers mobile money - Manuel (approuvé par admin/moderator)
- **Consulter l'historique** de leurs transactions

### Flux simplifié

**DÉPÔT:**
```
User → Initier dépôt → FusionPay → Payer → Webhook → Pièces créditées ✅
```

**RETRAIT:**
```
User → Demander retrait → Admin approuve → Mobile Money → Pièces débitées ✅
```

---

## Configuration Backend

### Variables d'environnement requises

Le système nécessite la configuration de FusionPay dans le fichier `.env`:

```bash
# FusionPay Payment Gateway Configuration
FUSIONPAY_API_URL=https://api.fusionpay.com
FUSIONPAY_API_KEY=your_fusionpay_api_key_here
```

**Étapes de configuration:**

1. **Obtenir les credentials FusionPay**
   - Créer un compte marchand sur FusionPay
   - Récupérer l'API Key depuis le dashboard

2. **Configurer les webhooks FusionPay**
   - URL du webhook: `https://votre-domaine.com/api/webhooks/fusionpay`
   - Événements à activer:
     - `payin.session.pending`
     - `payin.session.completed`
     - `payin.session.cancelled`

3. **Configurer les URLs de retour**
   - URL de retour: `https://votre-domaine.com/api/wallet/deposit/callback`
   - Cette page redirigera l'utilisateur après le paiement

4. **Tester la configuration**
   ```bash
   php artisan tinker
   >>> config('services.fusionpay.api_key')
   // Doit retourner votre clé API
   ```

### Migration de la base de données

La table `coin_transactions` sera créée automatiquement lors de l'exécution des migrations:

```bash
php artisan migrate
```

**Structure de la table:**
- Transactions de dépôt (automatiques via FusionPay)
- Transactions de retrait (manuelles avec validation admin)
- Historique complet avec statuts et montants
- Intégration FusionPay (token, transaction number, event)

---

## Informations générales

### Taux et frais
```
1 pièce = 500 FCFA

Dépôt:
- Frais: 7% (prélevés sur le montant payé)
- Exemple: Payer 10,000 FCFA → Recevoir 18.60 pièces
  (10,000 - 700 frais = 9,300 FCFA = 18.60 pièces)

Retrait:
- Frais: 0%
- Minimum: 5 pièces (2,500 FCFA)
- Exemple: Retirer 20 pièces → Recevoir 10,000 FCFA
```

### Statuts des transactions

| Status | Description | Type |
|--------|-------------|------|
| `pending` | En attente de traitement | Dépôt & Retrait |
| `processing` | Paiement en cours (FusionPay) | Dépôt uniquement |
| `completed` | Complété avec succès | Dépôt & Retrait |
| `cancelled` | Annulé par l'utilisateur ou timeout | Dépôt uniquement |
| `failed` | Échec du paiement | Dépôt uniquement |
| `rejected` | Rejeté par un admin | Retrait uniquement |

### Méthodes de paiement

Pour les retraits, les méthodes acceptées:
- `orange_money` - Orange Money
- `mtn_money` - MTN Money
- `moov_money` - Moov Money
- `wave` - Wave

---

## Endpoints Utilisateur

Base URL: `https://api.tourno.com/api/coin-wallet`

Tous ces endpoints requièrent l'authentification (`Authorization: Bearer {token}`)

### 1. Obtenir le solde de pièces

```http
GET /coin-wallet/balance
```

**Réponse:**
```json
{
  "success": true,
  "balance": 45.50
}
```

---

### 2. Initier un dépôt

```http
POST /coin-wallet/deposit/initiate
```

**Body:**
```json
{
  "amount_money": 10000
}
```

**Validation:**
- `amount_money`: requis, numérique, minimum 100 FCFA

**Réponse succès:**
```json
{
  "success": true,
  "message": "Dépôt initié avec succès",
  "data": {
    "transaction": {
      "uuid": "123e4567-e89b-12d3-a456-426614174000",
      "type": "deposit",
      "amount_money": 10000,
      "fee_amount": 700,
      "amount_coins": 18.60,
      "status": "processing",
      "created_at": "2025-01-05T10:30:00.000000Z"
    },
    "payment_url": "https://www.pay.moneyfusion.net/pay/6596aded36bd58823b084564",
    "token": "5d58823b084564"
  }
}
```

**Action frontend:**
1. Récupérer `payment_url`
2. Rediriger l'utilisateur vers cette URL
3. L'utilisateur paye sur FusionPay
4. FusionPay redirige vers: `https://app.tourno.com/wallet/deposit/success?token=xxx`
5. Afficher une page de succès/attente
6. Optionnel: Polling pour vérifier le statut via `GET /coin-wallet/transactions/{uuid}`

**Emails envoyés:**
- Email d'initiation avec lien de paiement
- Email de rappel après 10 minutes (si toujours pending)
- Email de confirmation quand complété

---

### 3. Demander un retrait

```http
POST /coin-wallet/withdrawal/request
```

**Body:**
```json
{
  "amount_coins": 20,
  "payment_phone": "01020304 05",
  "payment_method": "orange_money"
}
```

**Validation:**
- `amount_coins`: requis, numérique, minimum 5
- `payment_phone`: requis, string, max 20 caractères
- `payment_method`: requis, valeurs: `orange_money`, `mtn_money`, `moov_money`, `wave`

**Réponse succès:**
```json
{
  "success": true,
  "message": "Demande de retrait enregistrée avec succès",
  "data": {
    "uuid": "123e4567-e89b-12d3-a456-426614174001",
    "user_id": 1,
    "type": "withdrawal",
    "amount_coins": 20,
    "amount_money": 10000,
    "net_amount": 10000,
    "fee_amount": 0,
    "fee_percentage": 0,
    "payment_phone": "01 02 03 04 05",
    "payment_method": "orange_money",
    "status": "pending",
    "created_at": "2025-01-05T10:35:00.000000Z"
  }
}
```

**Réponse erreur (solde insuffisant):**
```json
{
  "success": false,
  "message": "Solde insuffisant. Vous avez 15.5 pièces."
}
```

**Réponse erreur (retrait déjà en attente):**
```json
{
  "success": false,
  "message": "Vous avez déjà une demande de retrait en attente de traitement."
}
```

**Action frontend:**
1. Afficher message de confirmation
2. Informer que le traitement prendra 24-48h
3. Rediriger vers l'historique des transactions

**Emails envoyés:**
- Email de confirmation à l'utilisateur
- Email de notification aux admins/moderators

**⚠️ Important:** Les pièces restent dans le wallet jusqu'à l'approbation par un admin.

---

### 4. Obtenir l'historique des transactions

```http
GET /coin-wallet/transactions?type=deposit&status=completed
```

**Query Parameters:**
- `type` (optionnel): `deposit` ou `withdrawal`
- `status` (optionnel): `pending`, `processing`, `completed`, `cancelled`, `failed`, `rejected`

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174000",
      "type": "deposit",
      "amount_coins": 18.60,
      "amount_money": 10000,
      "fee_amount": 700,
      "net_amount": 9300,
      "status": "completed",
      "created_at": "2025-01-05T10:30:00.000000Z",
      "processed_at": "2025-01-05T10:32:15.000000Z"
    },
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174001",
      "type": "withdrawal",
      "amount_coins": 20,
      "amount_money": 10000,
      "net_amount": 10000,
      "payment_phone": "01 02 03 04 05",
      "payment_method": "orange_money",
      "status": "pending",
      "created_at": "2025-01-05T10:35:00.000000Z",
      "processed_at": null,
      "processor": null
    }
  ]
}
```

---

### 5. Obtenir une transaction spécifique

```http
GET /coin-wallet/transactions/{uuid}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "type": "deposit",
    "amount_coins": 18.60,
    "amount_money": 10000,
    "fee_amount": 700,
    "fee_percentage": 7,
    "net_amount": 9300,
    "currency": "XOF",
    "status": "completed",
    "fusionpay_token": "5d58823b084564",
    "fusionpay_transaction_number": "0708889205",
    "fusionpay_event": "payin.session.completed",
    "created_at": "2025-01-05T10:30:00.000000Z",
    "processed_at": "2025-01-05T10:32:15.000000Z"
  }
}
```

---

## Endpoints Admin/Moderator

Base URL: `https://api.tourno.com/api/admin/coin-wallet`

⚠️ **Accès réservé:** Ces endpoints requièrent que `user.role` soit `admin` ou `moderator`.

### 1. Liste des retraits en attente

```http
GET /admin/coin-wallet/withdrawals/pending
```

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174001",
      "type": "withdrawal",
      "amount_coins": 20,
      "amount_money": 10000,
      "net_amount": 10000,
      "payment_phone": "01 02 03 04 05",
      "payment_method": "orange_money",
      "status": "pending",
      "created_at": "2025-01-05T10:35:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "01 02 03 04 05"
      }
    }
  ]
}
```

---

### 2. Tous les retraits (avec filtres)

```http
GET /admin/coin-wallet/withdrawals?status=completed&limit=100
```

**Query Parameters:**
- `status` (optionnel): filtrer par statut
- `limit` (optionnel): nombre max de résultats (défaut: 50)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174001",
      "type": "withdrawal",
      "amount_coins": 20,
      "amount_money": 10000,
      "net_amount": 10000,
      "payment_phone": "01 02 03 04 05",
      "payment_method": "orange_money",
      "status": "completed",
      "admin_note": "Paiement effectué via Orange Money",
      "created_at": "2025-01-05T10:35:00.000000Z",
      "processed_at": "2025-01-05T11:20:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "01 02 03 04 05"
      },
      "processor": {
        "id": 5,
        "name": "Admin User"
      }
    }
  ]
}
```

---

### 3. Approuver un retrait

```http
POST /admin/coin-wallet/withdrawals/{uuid}/approve
```

**Body:**
```json
{
  "admin_note": "Paiement effectué via Orange Money le 05/01/2025 à 11h20"
}
```

**Validation:**
- `admin_note` (optionnel): string, max 500 caractères

**Réponse succès:**
```json
{
  "success": true,
  "message": "Retrait approuvé avec succès",
  "data": {
    "uuid": "123e4567-e89b-12d3-a456-426614174001",
    "status": "completed",
    "processed_by": 5,
    "processed_at": "2025-01-05T11:20:00.000000Z",
    "admin_note": "Paiement effectué via Orange Money le 05/01/2025 à 11h20"
  }
}
```

**Réponse erreur (solde insuffisant):**
```json
{
  "success": false,
  "message": "Solde insuffisant. L'utilisateur a seulement 15.5 pièces."
}
```

**Action backend:**
1. Débite les pièces du wallet de l'utilisateur
2. Marque la transaction comme `completed`
3. Envoie un email de confirmation à l'utilisateur

**⚠️ Important:** L'admin doit d'abord effectuer le paiement mobile money AVANT d'approuver dans le système.

---

### 4. Rejeter un retrait

```http
POST /admin/coin-wallet/withdrawals/{uuid}/reject
```

**Body:**
```json
{
  "rejection_reason": "Numéro de téléphone invalide. Veuillez vérifier et soumettre une nouvelle demande."
}
```

**Validation:**
- `rejection_reason`: **requis**, string, max 500 caractères

**Réponse succès:**
```json
{
  "success": true,
  "message": "Retrait rejeté",
  "data": {
    "uuid": "123e4567-e89b-12d3-a456-426614174001",
    "status": "rejected",
    "processed_by": 5,
    "processed_at": "2025-01-05T11:25:00.000000Z",
    "rejection_reason": "Numéro de téléphone invalide. Veuillez vérifier et soumettre une nouvelle demande."
  }
}
```

**Action backend:**
1. Marque la transaction comme `rejected`
2. Les pièces restent dans le wallet (elles n'ont jamais été débitées)
3. Envoie un email à l'utilisateur avec la raison du rejet

---

### 5. Tous les dépôts (monitoring)

```http
GET /admin/coin-wallet/deposits?status=completed&limit=100
```

**Query Parameters:**
- `status` (optionnel): filtrer par statut
- `limit` (optionnel): nombre max de résultats (défaut: 50)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174000",
      "type": "deposit",
      "amount_coins": 18.60,
      "amount_money": 10000,
      "fee_amount": 700,
      "net_amount": 9300,
      "status": "completed",
      "fusionpay_token": "5d58823b084564",
      "fusionpay_transaction_number": "0708889205",
      "created_at": "2025-01-05T10:30:00.000000Z",
      "processed_at": "2025-01-05T10:32:15.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ]
}
```

---

### 6. Toutes les transactions (dépôts + retraits)

```http
GET /admin/coin-wallet/transactions?type=withdrawal&status=pending&limit=100
```

**Query Parameters:**
- `type` (optionnel): `deposit` ou `withdrawal`
- `status` (optionnel): filtrer par statut
- `limit` (optionnel): nombre max de résultats (défaut: 100)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174001",
      "type": "withdrawal",
      "amount_coins": 20,
      "amount_money": 10000,
      "status": "pending",
      "created_at": "2025-01-05T10:35:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "processor": null
    },
    {
      "uuid": "123e4567-e89b-12d3-a456-426614174000",
      "type": "deposit",
      "amount_coins": 18.60,
      "amount_money": 10000,
      "status": "completed",
      "created_at": "2025-01-05T10:30:00.000000Z",
      "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com"
      },
      "processor": null
    }
  ]
}
```

---

## Interface Admin à implémenter

### Page: Gestion des Transactions Coin Wallet

#### 1. Section "Demandes de retrait en attente" (Prioritaire)

**Endpoint:** `GET /admin/coin-wallet/withdrawals/pending`

**Composants à afficher:**

```
┌────────────────────────────────────────────────────────────────┐
│  🔔 DEMANDES DE RETRAIT EN ATTENTE (3)                          │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Demande #1                                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 👤 John Doe (john@example.com)                           │  │
│  │ 📅 05/01/2025 à 10:35                                    │  │
│  │                                                           │  │
│  │ 💰 Montant: 20 pièces → 10,000 FCFA                      │  │
│  │ 📱 Numéro: 01 02 03 04 05                                │  │
│  │ 💳 Méthode: Orange Money                                 │  │
│  │                                                           │  │
│  │ Transaction ID: 123e4567-e89b-12d3-a456-426614174001     │  │
│  │                                                           │  │
│  │ [Note admin (optionnel)] _________________________       │  │
│  │                                                           │  │
│  │ [ ✅ Approuver ]  [ ❌ Rejeter ]                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Demande #2...                                                  │
│                                                                 │
└────────────────────────────────────────────────────────────────┘
```

**Workflow d'approbation:**
1. Admin effectue le paiement mobile money manuellement
2. Admin clique sur "Approuver"
3. Modal de confirmation:
   - "Confirmez-vous avoir effectué le paiement de 10,000 FCFA vers 01 02 03 04 05 ?"
   - Champ optionnel "Note" pour l'historique
4. Appel API: `POST /admin/coin-wallet/withdrawals/{uuid}/approve`
5. Toast de succès + email envoyé à l'utilisateur

**Workflow de rejet:**
1. Admin clique sur "Rejeter"
2. Modal demandant la raison (obligatoire):
   - "Raison du rejet" (textarea)
3. Appel API: `POST /admin/coin-wallet/withdrawals/{uuid}/reject`
4. Toast de succès + email envoyé à l'utilisateur

---

#### 2. Section "Historique des retraits"

**Endpoint:** `GET /admin/coin-wallet/withdrawals`

**Filtres:**
- Par statut: Tous | En attente | Complétés | Rejetés
- Limite: 50 | 100 | 200

**Colonnes du tableau:**
| Date | Utilisateur | Montant | Numéro | Méthode | Statut | Traité par | Actions |
|------|-------------|---------|--------|---------|--------|------------|---------|
| 05/01 10:35 | John Doe | 10,000 FCFA (20 pièces) | 01 02... | Orange Money | ✅ Complété | Admin User | 👁️ Voir |
| 04/01 15:20 | Jane Smith | 5,000 FCFA (10 pièces) | 06 07... | MTN Money | ❌ Rejeté | Moderator | 👁️ Voir |

**Badges de statut:**
- `pending`: Badge jaune "En attente"
- `completed`: Badge vert "Complété"
- `rejected`: Badge rouge "Rejeté"

---

#### 3. Section "Historique des dépôts"

**Endpoint:** `GET /admin/coin-wallet/deposits`

**Filtres:**
- Par statut: Tous | En cours | Complétés | Annulés | Échoués
- Limite: 50 | 100 | 200

**Colonnes du tableau:**
| Date | Utilisateur | Montant payé | Frais | Pièces reçues | Statut | Token FusionPay |
|------|-------------|--------------|-------|---------------|--------|-----------------|
| 05/01 10:30 | John Doe | 10,000 FCFA | 700 | 18.60 | ✅ Complété | 5d58823b... |
| 05/01 09:15 | Jane Smith | 5,000 FCFA | 350 | 9.30 | 🔄 En cours | 4a47712a... |

**Badges de statut:**
- `pending`: Badge gris "En attente"
- `processing`: Badge bleu "En cours"
- `completed`: Badge vert "Complété"
- `cancelled`: Badge orange "Annulé"
- `failed`: Badge rouge "Échoué"

---

#### 4. Section "Vue globale des transactions"

**Endpoint:** `GET /admin/coin-wallet/transactions`

**Filtres:**
- Par type: Tous | Dépôts | Retraits
- Par statut: Tous | [statuts selon le type]
- Limite: 100 | 200 | 500

**Tableau mixte dépôts/retraits:**
| Date | Type | Utilisateur | Montant | Statut | Actions |
|------|------|-------------|---------|--------|---------|
| 05/01 10:35 | 💸 Retrait | John Doe | 10,000 FCFA | ⏳ En attente | Traiter |
| 05/01 10:30 | 💰 Dépôt | John Doe | 18.60 pièces | ✅ Complété | Voir |
| 04/01 15:20 | 💸 Retrait | Jane Smith | 5,000 FCFA | ❌ Rejeté | Voir |

---

### Statistiques à afficher (Dashboard)

**Endpoint:** Créer un endpoint custom ou agréger côté frontend

**Cartes de statistiques:**
```
┌─────────────────────┬─────────────────────┬─────────────────────┐
│ 🔔 En attente       │ ✅ Complétés (24h)  │ 📊 Volume total     │
│                     │                     │                     │
│ 3 retraits          │ 45 transactions     │ 2,450 pièces        │
│ 45,000 FCFA         │ 1,200,000 FCFA      │ 1,225,000 FCFA      │
└─────────────────────┴─────────────────────┴─────────────────────┘
```

---

## Gestion des erreurs

### Erreurs communes

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```
→ Token expiré ou invalide, rediriger vers login

**403 Forbidden:**
```json
{
  "success": false,
  "message": "Accès refusé. Réservé aux admins et moderators."
}
```
→ L'utilisateur n'a pas les droits (pas admin/moderator)

**404 Not Found:**
```json
{
  "success": false,
  "message": "Transaction introuvable"
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "amount_coins": ["The amount coins must be at least 5."],
    "payment_method": ["The selected payment method is invalid."]
  }
}
```

**400 Bad Request:**
```json
{
  "success": false,
  "message": "Solde insuffisant. Vous avez 15.5 pièces."
}
```

---

## Exemples de code

### React/Next.js - Initier un dépôt

```typescript
'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

export default function DepositPage() {
  const router = useRouter();
  const [amount, setAmount] = useState('');
  const [loading, setLoading] = useState(false);

  const calculateCoins = (amountMoney: number) => {
    const fee = amountMoney * 0.07;
    const netAmount = amountMoney - fee;
    const coins = netAmount / 500;
    return { coins, fee };
  };

  const handleDeposit = async () => {
    setLoading(true);

    try {
      const response = await fetch('https://api.tourno.com/api/coin-wallet/deposit/initiate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          amount_money: parseFloat(amount)
        })
      });

      const data = await response.json();

      if (data.success) {
        // Rediriger vers la page de paiement FusionPay
        window.location.href = data.data.payment_url;
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  const { coins, fee } = amount ? calculateCoins(parseFloat(amount)) : { coins: 0, fee: 0 };

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Déposer des pièces</h1>

      <div className="bg-white p-6 rounded-lg shadow">
        <label className="block mb-2">Montant à payer (FCFA)</label>
        <input
          type="number"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          className="border rounded px-4 py-2 w-full"
          placeholder="10000"
          min="100"
        />

        {amount && (
          <div className="mt-4 p-4 bg-gray-50 rounded">
            <p>Frais (7%): <span className="font-semibold">{fee.toFixed(0)} FCFA</span></p>
            <p>Pièces que vous recevrez: <span className="font-semibold text-green-600">{coins.toFixed(2)} pièces</span></p>
          </div>
        )}

        <button
          onClick={handleDeposit}
          disabled={!amount || loading}
          className="mt-4 bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 disabled:bg-gray-400"
        >
          {loading ? 'Chargement...' : 'Continuer vers le paiement'}
        </button>
      </div>
    </div>
  );
}
```

---

### React/Next.js - Demander un retrait

```typescript
'use client';

import { useState } from 'react';

export default function WithdrawalPage() {
  const [formData, setFormData] = useState({
    amount_coins: '',
    payment_phone: '',
    payment_method: 'orange_money'
  });
  const [loading, setLoading] = useState(false);

  const calculateMoney = (coins: number) => {
    return coins * 500;
  };

  const handleWithdrawal = async () => {
    setLoading(true);

    try {
      const response = await fetch('https://api.tourno.com/api/coin-wallet/withdrawal/request', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          amount_coins: parseFloat(formData.amount_coins),
          payment_phone: formData.payment_phone,
          payment_method: formData.payment_method
        })
      });

      const data = await response.json();

      if (data.success) {
        alert('Demande de retrait enregistrée ! Vous recevrez un email de confirmation.');
        // Rediriger vers l'historique
        window.location.href = '/wallet/transactions';
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  const money = formData.amount_coins ? calculateMoney(parseFloat(formData.amount_coins)) : 0;

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Retirer des pièces</h1>

      <div className="bg-white p-6 rounded-lg shadow">
        <div className="mb-4">
          <label className="block mb-2">Nombre de pièces (minimum 5)</label>
          <input
            type="number"
            value={formData.amount_coins}
            onChange={(e) => setFormData({ ...formData, amount_coins: e.target.value })}
            className="border rounded px-4 py-2 w-full"
            placeholder="20"
            min="5"
          />
        </div>

        {formData.amount_coins && (
          <div className="mb-4 p-4 bg-green-50 rounded">
            <p>Montant que vous recevrez: <span className="font-semibold text-green-600">{money.toLocaleString()} FCFA</span></p>
            <p className="text-sm text-gray-600">Pas de frais sur les retraits</p>
          </div>
        )}

        <div className="mb-4">
          <label className="block mb-2">Numéro de paiement</label>
          <input
            type="text"
            value={formData.payment_phone}
            onChange={(e) => setFormData({ ...formData, payment_phone: e.target.value })}
            className="border rounded px-4 py-2 w-full"
            placeholder="01 02 03 04 05"
          />
        </div>

        <div className="mb-4">
          <label className="block mb-2">Méthode de paiement</label>
          <select
            value={formData.payment_method}
            onChange={(e) => setFormData({ ...formData, payment_method: e.target.value })}
            className="border rounded px-4 py-2 w-full"
          >
            <option value="orange_money">Orange Money</option>
            <option value="mtn_money">MTN Money</option>
            <option value="moov_money">Moov Money</option>
            <option value="wave">Wave</option>
          </select>
        </div>

        <div className="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
          <p className="text-sm">⏱️ Votre demande sera traitée dans les 24-48 heures par notre équipe.</p>
        </div>

        <button
          onClick={handleWithdrawal}
          disabled={!formData.amount_coins || !formData.payment_phone || loading}
          className="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 disabled:bg-gray-400 w-full"
        >
          {loading ? 'Envoi...' : 'Demander le retrait'}
        </button>
      </div>
    </div>
  );
}
```

---

### React/Next.js - Interface Admin (Demandes en attente)

```typescript
'use client';

import { useState, useEffect } from 'react';

interface Withdrawal {
  uuid: string;
  amount_coins: number;
  amount_money: number;
  net_amount: number;
  payment_phone: string;
  payment_method: string;
  created_at: string;
  user: {
    id: number;
    name: string;
    email: string;
    phone: string;
  };
}

export default function AdminWithdrawalsPage() {
  const [withdrawals, setWithdrawals] = useState<Withdrawal[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPendingWithdrawals();
  }, []);

  const fetchPendingWithdrawals = async () => {
    try {
      const response = await fetch('https://api.tourno.com/api/admin/coin-wallet/withdrawals/pending', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });

      const data = await response.json();
      if (data.success) {
        setWithdrawals(data.data);
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (uuid: string) => {
    const note = prompt('Note admin (optionnel):');

    if (!confirm('Confirmez-vous avoir effectué le paiement mobile money ?')) {
      return;
    }

    try {
      const response = await fetch(`https://api.tourno.com/api/admin/coin-wallet/withdrawals/${uuid}/approve`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ admin_note: note || undefined })
      });

      const data = await response.json();

      if (data.success) {
        alert('Retrait approuvé avec succès !');
        fetchPendingWithdrawals(); // Refresh
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Une erreur est survenue');
    }
  };

  const handleReject = async (uuid: string) => {
    const reason = prompt('Raison du rejet (obligatoire):');

    if (!reason) {
      alert('La raison du rejet est obligatoire');
      return;
    }

    try {
      const response = await fetch(`https://api.tourno.com/api/admin/coin-wallet/withdrawals/${uuid}/reject`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ rejection_reason: reason })
      });

      const data = await response.json();

      if (data.success) {
        alert('Retrait rejeté');
        fetchPendingWithdrawals(); // Refresh
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Une erreur est survenue');
    }
  };

  if (loading) return <div>Chargement...</div>;

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">
        🔔 Demandes de retrait en attente ({withdrawals.length})
      </h1>

      {withdrawals.length === 0 ? (
        <p className="text-gray-500">Aucune demande en attente</p>
      ) : (
        <div className="space-y-4">
          {withdrawals.map((withdrawal) => (
            <div key={withdrawal.uuid} className="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-400">
              <div className="flex justify-between items-start">
                <div>
                  <h3 className="font-semibold text-lg">👤 {withdrawal.user.name}</h3>
                  <p className="text-sm text-gray-600">{withdrawal.user.email}</p>
                  <p className="text-sm text-gray-500">
                    📅 {new Date(withdrawal.created_at).toLocaleString('fr-FR')}
                  </p>
                </div>
              </div>

              <div className="mt-4 p-4 bg-gray-50 rounded">
                <p>💰 Montant: <span className="font-semibold">{withdrawal.amount_coins} pièces → {withdrawal.net_amount.toLocaleString()} FCFA</span></p>
                <p>📱 Numéro: <span className="font-semibold">{withdrawal.payment_phone}</span></p>
                <p>💳 Méthode: <span className="font-semibold">{withdrawal.payment_method.replace('_', ' ')}</span></p>
                <p className="text-xs text-gray-500 mt-2">ID: {withdrawal.uuid}</p>
              </div>

              <div className="mt-4 flex gap-2">
                <button
                  onClick={() => handleApprove(withdrawal.uuid)}
                  className="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
                >
                  ✅ Approuver
                </button>
                <button
                  onClick={() => handleReject(withdrawal.uuid)}
                  className="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700"
                >
                  ❌ Rejeter
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

---

## Notes importantes

### Pour le développement
1. **Variables d'environnement:**
   ```env
   NEXT_PUBLIC_API_URL=https://api.tourno.com/api
   ```

2. **Stockage du token:**
   - Utiliser `localStorage` ou `cookies` sécurisés
   - Inclure dans chaque requête: `Authorization: Bearer {token}`

3. **Redirections:**
   - Après dépôt → FusionPay → Callback → `https://app.tourno.com/wallet/deposit/success?token=xxx`
   - Créer une page `/wallet/deposit/success` qui vérifie le statut

4. **Polling (optionnel):**
   - Pour vérifier le statut d'un dépôt en temps réel
   - Utiliser `GET /coin-wallet/transactions/{uuid}` toutes les 5 secondes
   - Arrêter quand `status !== 'pending' && status !== 'processing'`

### Pour la production
1. **URL API:** Remplacer `https://api.tourno.com` par l'URL réelle
2. **CORS:** Assurez-vous que le backend autorise votre domaine frontend
3. **HTTPS:** Toujours utiliser HTTPS en production
4. **Webhooks:** Les webhooks FusionPay doivent pointer vers: `https://api.tourno.com/api/webhooks/fusionpay`

---

## Support

Pour toute question ou problème:
- Backend: Consultez [COIN_WALLET_SCENARIOS.md](COIN_WALLET_SCENARIOS.md) pour la logique complète
- API Issues: Vérifier les logs Laravel
- FusionPay: Consultez leur documentation ou contactez leur support

---

**Dernière mise à jour:** 05 Janvier 2026
