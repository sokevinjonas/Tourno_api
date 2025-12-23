# 💰 Système de Solde Bloqué pour les Organisateurs

## 📌 Concept

Le système de **solde bloqué** (locked balance) garantit que les fonds des tournois sont sécurisés et ne peuvent pas être retirés par l'organisateur avant que les prix ne soient distribués aux gagnants.

### Schéma du Wallet d'un Organisateur

```
Wallet de l'Organisateur
├── balance: 100.00          → Solde disponible (peut être retiré)
└── blocked_balance: 32.00   → Solde bloqué (ne peut pas être retiré)
```

**Solde total = balance + blocked_balance = 132.00**

---

## 🔄 Cycle de Vie d'un Tournoi - Flux des Fonds

### 1️⃣ Phase d'Inscription (status: `open`)

**Quand un participant s'inscrit :**

```
POST /api/tournaments/{id}/register
Body: { "game_account_id": 123 }
```

**Transactions automatiques :**
1. **Débit** du wallet du participant : `-4.00`
2. **Crédit** du wallet de l'organisateur : `+4.00`

**Exemple avec 8 participants à 4.00 entry_fee :**
```
Organisateur:
  balance: 100.00 → 132.00
  blocked_balance: 0.00 (pas encore bloqué)
```

---

### 2️⃣ Démarrage du Tournoi (status: `open` → `in_progress`)

**Quand le tournoi démarre :**

```
POST /api/tournaments/{id}/start
```

**Opération de blocage automatique :**
- Les fonds des inscriptions sont **bloqués** sur le wallet de l'organisateur
- Ces fonds ne peuvent plus être retirés

```
Organisateur (avant):
  balance: 132.00
  blocked_balance: 0.00

Organisateur (après):
  balance: 100.00           → Solde disponible
  blocked_balance: 32.00    → 8 participants × 4.00 (BLOQUÉ)
```

**Table `tournament_wallet_locks` :**
```json
{
  "tournament_id": 1,
  "wallet_id": 5,
  "locked_amount": 32.00,
  "paid_out": 0.00,
  "status": "locked"
}
```

---

### 3️⃣ Fin du Tournoi - Distribution des Prix (status: `completed`)

**Quand le tournoi est terminé :**

```
POST /api/tournaments/{id}/complete
```

**Distribution automatique des prix :**

Exemple de prize_distribution :
```json
{
  "1st": 16.00,
  "2nd": 10.00,
  "3rd": 6.00
}
```

**Transactions créées :**
1. Gagnant 1er : `+16.00` (depuis les fonds bloqués)
2. Gagnant 2ème : `+10.00` (depuis les fonds bloqués)
3. Gagnant 3ème : `+6.00` (depuis les fonds bloqués)

**Table `tournament_wallet_locks` (mise à jour) :**
```json
{
  "tournament_id": 1,
  "locked_amount": 32.00,
  "paid_out": 32.00,      → Total distribué
  "status": "processing_payouts"
}
```

---

### 4️⃣ Libération des Fonds Restants (status: `payouts_completed`)

**Calcul du reste :**
```
Reste = locked_amount - paid_out
Reste = 32.00 - 32.00 = 0.00
```

**Si prize_distribution était différent :**
```json
{
  "1st": 15.00,
  "2nd": 10.00,
  "3rd": 5.00
}
```

```
Total distribué = 30.00
Reste = 32.00 - 30.00 = 2.00
```

**Libération automatique :**
```
Organisateur (après libération):
  balance: 100.00 + 2.00 = 102.00
  blocked_balance: 32.00 - 32.00 = 0.00
```

**Table `tournament_wallet_locks` (finale) :**
```json
{
  "tournament_id": 1,
  "locked_amount": 32.00,
  "paid_out": 30.00,
  "status": "released"
}
```

---

## 📡 Routes API - Wallet

### 1. Obtenir le Wallet de l'utilisateur connecté

```http
GET /api/wallet
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "wallet": {
    "id": 5,
    "user_id": 10,
    "balance": "100.00",
    "blocked_balance": "32.00",
    "created_at": "2025-01-01T10:00:00.000000Z",
    "updated_at": "2025-01-15T14:30:00.000000Z"
  }
}
```

---

### 2. Obtenir uniquement le solde

```http
GET /api/wallet/balance
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "balance": "100.00"
}
```

> ⚠️ **Note :** Cette route retourne uniquement le `balance` (solde disponible), **pas** le `blocked_balance`.

---

### 3. Historique des transactions

```http
GET /api/wallet/transactions?limit=50&offset=0
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "transactions": [
    {
      "id": 1,
      "wallet_id": 5,
      "user_id": 10,
      "type": "credit",
      "amount": "4.00",
      "balance_before": "96.00",
      "balance_after": "100.00",
      "reason": "tournament_entry_received",
      "description": "Entry fee received for tournament #1",
      "tournament_id": 1,
      "created_at": "2025-01-15T14:30:00.000000Z"
    },
    {
      "id": 2,
      "wallet_id": 5,
      "user_id": 10,
      "type": "debit",
      "amount": "4.00",
      "balance_before": "100.00",
      "balance_after": "96.00",
      "reason": "tournament_registration",
      "description": "Inscription au tournoi #1",
      "tournament_id": 1,
      "created_at": "2025-01-15T14:25:00.000000Z"
    }
  ],
  "pagination": {
    "limit": 50,
    "offset": 0,
    "total": 2
  }
}
```

**Types de transactions (`reason`) :**

| Reason | Type | Description |
|--------|------|-------------|
| `tournament_registration` | debit | Inscription d'un participant à un tournoi |
| `tournament_entry_received` | credit | Réception de l'entry fee par l'organisateur |
| `tournament_entry_refunded` | debit | Remboursement suite au retrait d'un participant (organisateur) |
| `refund` | credit | Remboursement reçu par le participant |
| `tournament_prize` | credit | Prix reçu en tant que gagnant |
| `admin_adjustment` | credit/debit | Ajustement manuel par un admin |

---

### 4. Statistiques du wallet

```http
GET /api/wallet/statistics
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "statistics": {
    "total_credited": "150.00",
    "total_debited": "50.00",
    "total_transactions": 12,
    "current_balance": "100.00"
  }
}
```

---

### 5. Admin : Ajouter des fonds

```http
POST /api/wallet/add-funds
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "user_id": 10,
  "amount": 50.00,
  "description": "Compensation pour un bug"
}
```

**Réponse :**
```json
{
  "message": "Funds added successfully",
  "transaction": {
    "id": 15,
    "type": "credit",
    "amount": "50.00",
    "reason": "admin_adjustment"
  },
  "new_balance": "150.00"
}
```

---

## 🚨 Cas Particuliers

### Retrait d'un Participant (avant le début)

```http
POST /api/tournaments/{id}/withdraw
Authorization: Bearer {token}
```

**Transactions automatiques :**
1. **Débit** du wallet de l'organisateur : `-4.00`
2. **Crédit** (remboursement) au wallet du participant : `+4.00`

**Conditions :**
- Le tournoi **ne doit pas** être en status `in_progress` ou `completed`
- Seul le participant inscrit peut se retirer

---

## ✅ Conditions pour Débloquer les Fonds

Les fonds bloqués sont **automatiquement débloqués** dans ces situations :

### 1. Tournoi Complété avec Distribution des Prix

**Condition :** Le tournoi doit être en status `completed` et tous les prix doivent être distribués.

**Processus :**
1. Distribution des prix aux gagnants
2. Calcul du reste : `locked_amount - paid_out`
3. Crédit du reste au wallet de l'organisateur
4. Réduction du `blocked_balance` à 0

---

### 2. Tournoi Annulé

**Condition :** Le tournoi est annulé (status `cancelled`)

**Processus :**
1. Remboursement de tous les participants inscrits
2. Débit du wallet de l'organisateur
3. Réduction du `blocked_balance`

---

## 🎯 Affichage Frontend - Recommandations

### Dashboard Organisateur

Afficher clairement les deux soldes :

```tsx
<WalletCard>
  <BalanceItem>
    <Label>Solde Disponible</Label>
    <Amount>{wallet.balance} €</Amount>
    <Subtitle>Peut être retiré</Subtitle>
  </BalanceItem>

  <BalanceItem variant="warning">
    <Label>Solde Bloqué</Label>
    <Amount>{wallet.blocked_balance} €</Amount>
    <Subtitle>Fonds de tournois en cours</Subtitle>
  </BalanceItem>

  <TotalBalance>
    <Label>Solde Total</Label>
    <Amount>{wallet.balance + wallet.blocked_balance} €</Amount>
  </TotalBalance>
</WalletCard>
```

---

### Détail d'un Tournoi en Cours

```tsx
<TournamentFundsCard>
  <Title>Fonds du Tournoi</Title>

  <InfoItem>
    <Label>Participants Inscrits</Label>
    <Value>8 / 32</Value>
  </InfoItem>

  <InfoItem>
    <Label>Entry Fee</Label>
    <Value>4.00 €</Value>
  </InfoItem>

  <InfoItem>
    <Label>Fonds Collectés</Label>
    <Value>{8 * 4.00} € (BLOQUÉ)</Value>
  </InfoItem>

  <Alert variant="info">
    Ces fonds sont bloqués et seront distribués aux gagnants à la fin du tournoi.
  </Alert>
</TournamentFundsCard>
```

---

## 📊 Exemple Complet - Timeline

### Tournoi avec 8 participants, entry_fee = 4.00 €

```
Initial: Organisateur balance = 100.00, blocked = 0.00

┌─ Inscription Participant 1
│  Participant 1: -4.00
│  Organisateur: +4.00
│  → Organisateur: balance = 104.00, blocked = 0.00
│
├─ Inscription Participant 2
│  → Organisateur: balance = 108.00, blocked = 0.00
│
├─ ... (6 autres inscriptions)
│  → Organisateur: balance = 132.00, blocked = 0.00
│
├─ Démarrage du Tournoi
│  → BLOCAGE des fonds
│  → Organisateur: balance = 100.00, blocked = 32.00
│
├─ Tournoi en cours...
│
├─ Fin du Tournoi
│  Distribution des prix:
│    - 1er: +16.00
│    - 2ème: +10.00
│    - 3ème: +6.00
│  Total distribué = 32.00
│
└─ Libération des Fonds
   Reste = 32.00 - 32.00 = 0.00
   → Organisateur: balance = 100.00, blocked = 0.00
```

---

## 🔐 Sécurité

- ✅ Les fonds bloqués **ne peuvent jamais être retirés** tant que le tournoi n'est pas terminé
- ✅ Les participants sont **toujours remboursés** en cas de retrait avant le début
- ✅ Les gagnants sont **garantis de recevoir leurs prix** car les fonds sont bloqués
- ✅ L'organisateur reçoit le **reste des fonds** (si prize_distribution < 100%)

---

## 📞 Support

Pour toute question concernant le système de wallet, contactez l'équipe backend.

**Date de dernière mise à jour :** 2025-12-23
