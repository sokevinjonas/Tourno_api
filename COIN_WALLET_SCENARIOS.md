# Scénarios de Dépôt et Retrait - Système de Pièces

## 📊 Informations Générales

- **Taux de conversion:** 1 pièce = 500 FCFA
- **Frais de dépôt:** 7% (prélevés sur le montant payé)
- **Frais de retrait:** 0%
- **Montant minimum de retrait:** 5 pièces (2,500 FCFA)
- **Provider de paiement:** FusionPay (MoneyFusion)

---

## 💰 SCÉNARIO 1: DÉPÔT DE PIÈCES (Automatique)

### Acteurs
- **Joueur ou Organisateur** (n'importe quel utilisateur)
- **FusionPay** (traitement automatique du paiement)
- **Système** (backend)

### Étapes du processus

#### 1️⃣ Initiation du dépôt

**Action utilisateur:**
```
L'utilisateur clique sur "Déposer des pièces" et entre le montant en FCFA
Exemple: 10,000 FCFA
```

**Calculs automatiques:**
```
Montant payé:     10,000 FCFA
Frais (7%):       -700 FCFA
Montant net:      9,300 FCFA
Pièces reçues:    18.60 pièces (9,300 ÷ 500)
```

**Backend:**
```php
// CoinWalletService::initiateDeposit()
1. Calculer les montants (amount_money, fees, amount_coins)
2. Créer CoinTransaction avec status='pending'
3. Appeler l'API FusionPay avec les données:
   - totalPrice: 10000
   - article: [{"Dépôt de pièces": 10000}]
   - numeroSend: téléphone de l'utilisateur
   - nomclient: nom de l'utilisateur
   - personal_Info: userId, transactionId, amountCoins
   - return_url: URL de redirection après paiement
   - webhook_url: URL pour recevoir les notifications

4. Recevoir la réponse FusionPay:
   {
     "statut": true,
     "token": "5d58823b084564",
     "message": "paiement en cours",
     "url": "https://www.pay.moneyfusion.net/pay/..."
   }

5. Sauvegarder le token et mettre status='processing'
6. **Envoyer EMAIL #1:** DepositInitiatedMail (avec lien de paiement)
7. **Planifier JOB:** RemindPendingDepositJob dans 10 minutes
8. Retourner l'URL de paiement au frontend
```

**Emails envoyés:**
- ✅ **DepositInitiatedMail** - "Dépôt de pièces initié - Finaliser votre paiement"
  - Contient le lien vers la page de paiement FusionPay
  - Détails du montant à payer et pièces à recevoir

#### 2️⃣ Paiement par l'utilisateur

**Action utilisateur:**
```
1. L'utilisateur est redirigé vers la page de paiement FusionPay
2. Il choisit son moyen de paiement (Orange Money, MTN, etc.)
3. Il finalise le paiement
```

**Notifications FusionPay:**
FusionPay envoie plusieurs webhooks pendant le processus:

**Webhook #1 - Paiement en attente:**
```json
{
  "event": "payin.session.pending",
  "tokenPay": "5d58823b084564",
  "numeroSend": "01010101",
  "nomclient": "John Doe",
  "numeroTransaction": "0708889205",
  "Montant": 10000,
  "frais": 6,
  "personal_Info": [{"userId": 1, "transactionId": "uuid", "amountCoins": 18.60}],
  "createdAt": "2025-05-09T12:50:45.412Z"
}
```

**Backend (webhook pending):**
```php
// CoinWalletService::processFusionPayWebhook()
1. Vérifier que la transaction existe (via tokenPay)
2. Vérifier qu'elle n'est pas déjà completed (idempotence)
3. Logger l'événement
4. Ne rien faire de plus (attendre la confirmation)
```

**Webhook #2 - Paiement complété:** (si succès)
```json
{
  "event": "payin.session.completed",
  "tokenPay": "5d58823b084564",
  "numeroSend": "01010101",
  "nomclient": "John Doe",
  "numeroTransaction": "0708889205",
  "Montant": 10000,
  "frais": 6,
  "personal_Info": [{"userId": 1, "transactionId": "uuid", "amountCoins": 18.60}],
  "createdAt": "2025-05-09T12:51:30.412Z"
}
```

**Backend (webhook completed):**
```php
// CoinWalletService::processFusionPayWebhook()
1. Vérifier que la transaction existe
2. Vérifier qu'elle n'est pas déjà completed (idempotence)
3. Charger le wallet de l'utilisateur
4. **CRÉDITER:** wallet.balance += amount_coins (18.60 pièces)
5. Mettre à jour la transaction: status='completed', processed_at=now()
6. **Envoyer EMAIL #2:** DepositCompletedMail
```

**Emails envoyés:**
- ✅ **DepositCompletedMail** - "Dépôt réussi"
  - Confirmation que les pièces ont été ajoutées
  - Récapitulatif: montant payé, frais, pièces reçues

**Webhook #3 - Paiement annulé:** (si échec ou annulation)
```json
{
  "event": "payin.session.cancelled",
  "tokenPay": "5d58823b084564",
  ...
}
```

**Backend (webhook cancelled):**
```php
// CoinWalletService::processFusionPayWebhook()
1. Mettre à jour la transaction: status='cancelled'
2. Logger l'annulation
3. NE PAS créditer le wallet
```

#### 3️⃣ Rappel après 10 minutes (si paiement non finalisé)

**Job automatique:**
```php
// RemindPendingDepositJob (lancé 10 minutes après l'initiation)
1. Recharger la transaction depuis la base
2. Vérifier si status IN ['pending', 'processing']
3. Si OUI: **Envoyer EMAIL #3:** DepositReminderMail
4. Si NON: Logger et ignorer (déjà complété ou annulé)
```

**Emails envoyés:**
- ⏰ **DepositReminderMail** - "Rappel - Finalisez votre dépôt de pièces"
  - Rappel qu'un paiement est en attente
  - Lien vers la page de paiement FusionPay
  - Avertissement que la transaction sera annulée

#### 4️⃣ Redirection après paiement (return_url)

**FusionPay:**
```
Après que l'utilisateur paye (succès ou échec), FusionPay le redirige vers:
https://api.tourno.com/api/wallet/deposit/callback?token=5d58823b084564
```

**Backend:**
```php
// WalletController::depositCallback()
1. Récupérer le token depuis l'URL
2. Rediriger vers le frontend:
   https://app.tourno.com/wallet/deposit/success?token=5d58823b084564
```

**Frontend:**
```
La page de succès peut:
1. Afficher un message de succès
2. Optionnellement vérifier le statut via une API
3. Rediriger vers le wallet après quelques secondes
```

### Résumé des emails - Dépôt

| Moment | Email | Condition |
|--------|-------|-----------|
| À l'initiation | DepositInitiatedMail | Toujours |
| Après 10 min | DepositReminderMail | Si status toujours pending/processing |
| À la complétion | DepositCompletedMail | Si webhook completed reçu |

---

## 💸 SCÉNARIO 2: RETRAIT DE PIÈCES (Manuel - Approuvé par Admin)

### Acteurs
- **Joueur ou Organisateur** (demandeur)
- **Admin ou Moderator** (approbateur)
- **Système** (backend)

### Étapes du processus

#### 1️⃣ Demande de retrait

**Action utilisateur:**
```
L'utilisateur clique sur "Retirer des pièces" et entre:
- Nombre de pièces: 20 pièces
- Numéro de téléphone pour le paiement: 01 02 03 04 05
- Méthode: Mobile Money (Orange, MTN, etc.)
```

**Calculs automatiques:**
```
Pièces retirées:  20 pièces
Montant brut:     10,000 FCFA (20 × 500)
Frais (0%):       0 FCFA
Montant net:      10,000 FCFA (ce que l'utilisateur recevra)
```

**Backend:**
```php
// CoinWalletService::requestWithdrawal()
1. Vérifier que l'utilisateur a un wallet
2. Vérifier le montant minimum (>= 5 pièces) ✅
3. Vérifier le solde disponible (wallet.balance >= 20) ✅
4. Vérifier qu'il n'a pas déjà un retrait pending ✅
5. Calculer les montants (amount_coins, amount_money, fees, net_amount)
6. Créer CoinTransaction avec status='pending'
   - type: 'withdrawal'
   - amount_coins: 20
   - amount_money: 10000
   - net_amount: 10000
   - payment_phone: 01 02 03 04 05
   - payment_method: 'mobile_money'
7. **Envoyer EMAIL #1:** WithdrawalRequestedMail (à l'utilisateur)
8. **Envoyer EMAIL #2:** WithdrawalRequestAdminMail (à tous les admins/moderators)
9. Retourner la transaction au frontend
```

**⚠️ IMPORTANT:** Les pièces NE sont PAS débitées du wallet à cette étape! Elles restent disponibles jusqu'à l'approbation.

**Emails envoyés:**
- ✅ **WithdrawalRequestedMail** (au demandeur) - "Demande de retrait en attente"
  - Confirmation que la demande a été reçue
  - Détails: pièces, montant, numéro de paiement
  - Délai de traitement: 24-48 heures

- 🔔 **WithdrawalRequestAdminMail** (aux admins) - "Nouvelle demande de retrait - Action requise"
  - Alerte qu'une demande nécessite traitement
  - Informations utilisateur: nom, email
  - Détails de la transaction: pièces, montant, numéro
  - Rappel: traiter dans les 48 heures

#### 2️⃣ Approbation par un Admin/Moderator

**Action admin:**
```
L'admin se connecte au panneau d'administration:
1. Voit la liste des retraits en attente
2. Vérifie les informations
3. Effectue le paiement mobile money vers 01 02 03 04 05
4. Clique sur "Approuver" et peut ajouter une note
```

**Backend:**
```php
// CoinWalletService::approveWithdrawal()
1. Vérifier que c'est bien un retrait ✅
2. Vérifier que status='pending' ✅
3. Vérifier que l'admin est bien admin ou moderator ✅
4. Dans une TRANSACTION DB:
   a. Recharger le wallet de l'utilisateur avec lock
   b. Vérifier ENCORE le solde (wallet.balance >= 20) ✅
   c. **DÉBITER:** wallet.balance -= amount_coins (20 pièces)
   d. Mettre à jour la transaction:
      - status='completed'
      - processed_by=admin_id
      - processed_at=now()
5. **Envoyer EMAIL #3:** WithdrawalCompletedMail
```

**Emails envoyés:**
- ✅ **WithdrawalCompletedMail** - "Retrait traité avec succès"
  - Confirmation que le paiement a été envoyé
  - Détails: pièces retirées, montant envoyé, numéro
  - Info: peut prendre quelques minutes pour apparaître

#### 3️⃣ Rejet par un Admin/Moderator (Alternative)

**Action admin:**
```
L'admin peut aussi rejeter la demande avec une raison:
Raison: "Numéro de téléphone invalide - veuillez vérifier"
```

**Backend:**
```php
// CoinWalletService::rejectWithdrawal()
1. Vérifier que c'est bien un retrait ✅
2. Vérifier que status='pending' ✅
3. Mettre à jour la transaction:
   - status='rejected'
   - rejection_reason='Numéro de téléphone invalide...'
   - processed_by=admin_id
   - processed_at=now()
4. **Envoyer EMAIL #4:** WithdrawalRejectedMail
```

**⚠️ IMPORTANT:** Les pièces restent dans le wallet de l'utilisateur (elles n'ont jamais été débitées).

**Emails envoyés:**
- ❌ **WithdrawalRejectedMail** - "Demande de retrait refusée"
  - Notification du rejet
  - Raison du rejet (expliquée par l'admin)
  - Confirmation que les pièces sont toujours disponibles
  - Invitation à contacter le support si erreur

### Résumé des emails - Retrait

| Moment | Email | Destinataire | Condition |
|--------|-------|--------------|-----------|
| À la demande | WithdrawalRequestedMail | Utilisateur | Toujours |
| À la demande | WithdrawalRequestAdminMail | Admins/Moderators | Toujours |
| À l'approbation | WithdrawalCompletedMail | Utilisateur | Si approuvé |
| Au rejet | WithdrawalRejectedMail | Utilisateur | Si rejeté |

---

## 📋 Différences Clés: Dépôt vs Retrait

| Aspect | Dépôt | Retrait |
|--------|-------|---------|
| **Automatisation** | ✅ Automatique via FusionPay | ❌ Manuel (admin/moderator) |
| **Frais** | 7% sur le montant payé | 0% |
| **Temps de traitement** | Immédiat (quelques secondes) | 24-48 heures |
| **Provider** | FusionPay | Mobile Money direct |
| **Webhook** | Oui (FusionPay) | Non |
| **Emails** | 2-3 emails | 3-4 emails |
| **Débit/Crédit** | Crédit immédiat après webhook | Débit seulement après approbation |
| **Montant minimum** | Aucun | 5 pièces (2,500 FCFA) |
| **Vérifications** | Automatiques | Manuelles par admin |

---

## 🔒 Sécurité et Idempotence

### Dépôt (Webhook FusionPay)
```php
// Protection contre les webhooks multiples
if ($transaction->isCompleted()) {
    Log::info("Transaction already completed, skipping");
    return; // Idempotence: ne rien faire
}

// Lock de transaction pour éviter les race conditions
$transaction = CoinTransaction::where('fusionpay_token', $tokenPay)
    ->lockForUpdate()
    ->first();
```

### Retrait (Approbation)
```php
// Vérifier le solde DEUX FOIS:
// 1. Avant de créer la demande
if ($user->wallet->balance < $amountCoins) {
    throw new \Exception('Solde insuffisant');
}

// 2. Pendant l'approbation (dans une transaction DB)
DB::transaction(function () use ($transaction, $admin) {
    $user->wallet->lockForUpdate(); // Lock pour éviter les retraits simultanés

    if ($user->wallet->balance < $transaction->amount_coins) {
        throw new \Exception('Solde insuffisant');
    }

    $user->wallet->decrement('balance', $transaction->amount_coins);
});
```

---

## 🔗 Configuration Requise

### .env
```env
# FusionPay
FUSIONPAY_API_URL=https://your-fusionpay-api-url
FUSIONPAY_API_KEY=your_api_key_here

# Frontend URL pour redirections
APP_FRONTEND_URL=https://app.tourno.com
```

### config/services.php
```php
'fusionpay' => [
    'api_url' => env('FUSIONPAY_API_URL'),
    'api_key' => env('FUSIONPAY_API_KEY'),
],
```

---

## 📊 Diagrammes de Flux

### Flux de Dépôt
```
Utilisateur → Backend: Initier dépôt (10,000 FCFA)
Backend → DB: Créer CoinTransaction (status=pending)
Backend → FusionPay API: POST /payment
FusionPay API → Backend: {token, url}
Backend → DB: Update transaction (status=processing, token)
Backend → Email: DepositInitiatedMail
Backend → Job Queue: RemindPendingDepositJob (10 min)
Backend → Utilisateur: Retourner payment_url

Utilisateur → FusionPay: Payer sur la page
FusionPay → Backend Webhook: payin.session.pending
Backend → Log: Événement logged

FusionPay → Backend Webhook: payin.session.completed
Backend → DB: wallet.balance += 18.60
Backend → DB: transaction.status = completed
Backend → Email: DepositCompletedMail
Backend → FusionPay: 200 OK

FusionPay → Utilisateur: Redirection (return_url)
Backend Callback → Utilisateur: Redirect to frontend/success
```

### Flux de Retrait
```
Utilisateur → Backend: Demander retrait (20 pièces)
Backend → DB: Vérifier solde >= 20 ✅
Backend → DB: Créer CoinTransaction (status=pending)
Backend → Email: WithdrawalRequestedMail (utilisateur)
Backend → Email: WithdrawalRequestAdminMail (admins)
Backend → Utilisateur: Confirmation demande enregistrée

Admin → Backend: Effectuer paiement mobile money
Admin → Backend: Cliquer "Approuver"
Backend → DB: START TRANSACTION
Backend → DB: wallet.balance -= 20 (avec lock)
Backend → DB: transaction.status = completed
Backend → DB: COMMIT
Backend → Email: WithdrawalCompletedMail
Backend → Admin: Succès
```

---

## ⚠️ Cas d'Erreur et Gestion

### Dépôt - Erreurs possibles
1. **API FusionPay indisponible**
   - Transaction marquée status='failed'
   - Exception retournée au frontend
   - Pas d'email envoyé

2. **Webhook multiple pour même transaction**
   - Vérifié via isCompleted()
   - Ignoré en silence
   - Logged pour audit

3. **Webhook pour transaction inexistante**
   - Logged en warning
   - Ignoré
   - 200 OK retourné quand même

### Retrait - Erreurs possibles
1. **Solde insuffisant à l'approbation**
   - Exception lancée
   - Transaction reste status='pending'
   - Admin alerté de l'erreur

2. **Demande de retrait < 5 pièces**
   - Rejetée immédiatement
   - Exception retournée
   - Pas de transaction créée

3. **Déjà un retrait pending**
   - Exception: "Vous avez déjà une demande en attente"
   - Pas de nouvelle transaction créée

---

## 📧 Récapitulatif des Emails

### Dépôt (3 emails possibles)
1. **DepositInitiatedMail** - Toujours envoyé
2. **DepositReminderMail** - Envoyé après 10 min si toujours pending
3. **DepositCompletedMail** - Envoyé quand webhook completed reçu

### Retrait (4 emails possibles)
1. **WithdrawalRequestedMail** - Toujours envoyé (utilisateur)
2. **WithdrawalRequestAdminMail** - Toujours envoyé (admins)
3. **WithdrawalCompletedMail** - Si approuvé
4. **WithdrawalRejectedMail** - Si rejeté

**Total emails créés:** 7
**Total jobs créés:** 1 (RemindPendingDepositJob)
