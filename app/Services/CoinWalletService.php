<?php

namespace App\Services;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Mail\DepositCompletedMail;
use App\Mail\DepositInitiatedMail;
use App\Mail\WithdrawalCompletedMail;
use App\Mail\WithdrawalRejectedMail;
use App\Mail\WithdrawalRequestAdminMail;
use App\Mail\WithdrawalRequestedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CoinWalletService
{
    // Constantes
    const COIN_TO_MONEY_RATE = 500; // 1 pièce = 500 FCFA
    const DEPOSIT_FEE_PERCENTAGE = 7.00; // 7% de frais sur dépôt
    const WITHDRAWAL_FEE_PERCENTAGE = 0.00; // Pas de frais sur retrait
    const MIN_WITHDRAWAL_COINS = 5; // Minimum 5 pièces pour retrait
    const CURRENCY = 'XOF';

    // FusionPay configuration
    protected ?string $apiUrl;
    protected string $webhookUrl;
    protected string $returnUrl;

    public function __construct()
    {
        // YOUR_API_URL doit être obtenu depuis le tableau de bord FusionPay
        $this->apiUrl = config('services.fusionpay.api_url');
        $this->webhookUrl = config('app.url') . '/api/webhooks/fusionpay';
        $this->returnUrl = config('services.fusionpay.return_url', config('app.url'));
    }

    /**
     * Calculer les montants pour un dépôt
     */
    public function calculateDepositAmounts(float $amountMoney): array
    {
        $feeAmount = ($amountMoney * self::DEPOSIT_FEE_PERCENTAGE) / 100;
        $netAmount = $amountMoney - $feeAmount;
        $amountCoins = $netAmount / self::COIN_TO_MONEY_RATE;

        return [
            'amount_money' => round($amountMoney, 2),
            'fee_percentage' => self::DEPOSIT_FEE_PERCENTAGE,
            'fee_amount' => round($feeAmount, 2),
            'net_amount' => round($netAmount, 2),
            'amount_coins' => round($amountCoins, 2),
        ];
    }

    /**
     * Calculer les montants pour un retrait
     */
    public function calculateWithdrawalAmounts(float $amountCoins): array
    {
        $amountMoney = $amountCoins * self::COIN_TO_MONEY_RATE;
        $feeAmount = ($amountMoney * self::WITHDRAWAL_FEE_PERCENTAGE) / 100;
        $netAmount = $amountMoney - $feeAmount;

        return [
            'amount_coins' => round($amountCoins, 2),
            'amount_money' => round($amountMoney, 2),
            'fee_percentage' => self::WITHDRAWAL_FEE_PERCENTAGE,
            'fee_amount' => round($feeAmount, 2),
            'net_amount' => round($netAmount, 2),
        ];
    }

    /**
     * Initier un dépôt via FusionPay
     */
    public function initiateDeposit(User $user, float $amountMoney): array
    {
        // Calculer les montants
        $amounts = $this->calculateDepositAmounts($amountMoney);

        // Créer la transaction en base
        $transaction = DB::transaction(function () use ($user, $amounts) {
            return CoinTransaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount_coins' => $amounts['amount_coins'],
                'amount_money' => $amounts['amount_money'],
                'fee_percentage' => $amounts['fee_percentage'],
                'fee_amount' => $amounts['fee_amount'],
                'net_amount' => $amounts['net_amount'],
                'currency' => self::CURRENCY,
                'payment_provider' => 'fusionpay',
                'status' => 'pending',
            ]);
        });

        Log::info("Deposit initiated for user {$user->id}, transaction {$transaction->uuid}");

        // Appeler l'API FusionPay selon leur documentation
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'totalPrice' => (int) $amounts['amount_money'],
                'article' => [
                    ['Dépôt de pièces' => (int) $amounts['amount_money']]
                ],
                'numeroSend' => $user->phone ?? '',
                'nomclient' => $user->name,
                'personal_Info' => [
                    [
                        'userId' => $user->id,
                        'transactionId' => $transaction->uuid,
                        'amountCoins' => $amounts['amount_coins'],
                    ]
                ],
                'apiReturnUrl' => $this->returnUrl,
                'webhook_url' => $this->webhookUrl,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Vérifier le statut de la réponse
                if (!($data['statut'] ?? false)) {
                    throw new \Exception('FusionPay returned error: ' . ($data['message'] ?? 'Unknown error'));
                }

                // Sauvegarder le token (pas tokenPay dans la réponse initiale)
                $transaction->update([
                    'fusionpay_token' => $data['token'],
                    'status' => 'processing',
                ]);

                Log::info("FusionPay payment created: {$data['token']}");

                $paymentUrl = $data['url'];

                // Envoyer email d'initiation avec le lien de paiement
                Mail::to($user)->send(new DepositInitiatedMail($user, $transaction, $paymentUrl));

                // Planifier un job de rappel dans 10 minutes
                \App\Jobs\RemindPendingDepositJob::dispatch($transaction, $paymentUrl)
                    ->delay(now()->addMinutes(10));

                return [
                    'success' => true,
                    'transaction' => $transaction,
                    'payment_url' => $paymentUrl,
                    'token' => $data['token'],
                    'message' => $data['message'] ?? 'Paiement en cours',
                ];
            } else {
                throw new \Exception('FusionPay API error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("FusionPay API error: " . $e->getMessage());

            $transaction->update([
                'status' => 'failed',
                'admin_note' => 'Erreur API FusionPay: ' . $e->getMessage(),
            ]);

            throw new \Exception('Impossible de créer le paiement. Veuillez réessayer.');
        }
    }

    /**
     * Traiter le webhook FusionPay
     */
    public function processFusionPayWebhook(array $payload): void
    {
        Log::info('FusionPay webhook received', $payload);

        $tokenPay = $payload['tokenPay'] ?? null;
        $event = $payload['event'] ?? null;

        if (!$tokenPay) {
            Log::warning('FusionPay webhook without tokenPay');
            return;
        }

        DB::transaction(function () use ($tokenPay, $event, $payload) {
            $transaction = CoinTransaction::where('fusionpay_token', $tokenPay)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::warning("Transaction not found for tokenPay: {$tokenPay}");
                return;
            }

            // Si déjà completed, ignorer (idempotence)
            if ($transaction->isCompleted()) {
                Log::info("Transaction {$transaction->uuid} already completed, skipping");
                return;
            }

            // Mettre à jour l'événement
            $transaction->fusionpay_event = $event;
            $transaction->fusionpay_transaction_number = $payload['numeroTransaction'] ?? null;

            // Traiter selon l'événement
            if ($event === 'payin.session.completed') {
                // Créditer le wallet
                $user = $transaction->user;
                $user->load('wallet');

                if (!$user->wallet) {
                    Log::error("User {$user->id} has no wallet");
                    return;
                }

                $user->wallet->increment('balance', $transaction->amount_coins);

                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                Log::info("Deposit completed: {$transaction->amount_coins} coins added to user {$user->id}");

                // Envoyer email de confirmation
                Mail::to($user)->send(new DepositCompletedMail($user, $transaction));

            } elseif ($event === 'payin.session.cancelled') {
                $transaction->update([
                    'status' => 'cancelled',
                ]);

                Log::info("Deposit cancelled for transaction {$transaction->uuid}");

            } elseif ($event === 'payin.session.pending') {
                // Toujours en traitement
                Log::info("Deposit still pending for transaction {$transaction->uuid}");
            }

            $transaction->save();
        });
    }

    /**
     * Demander un retrait
     */
    public function requestWithdrawal(User $user, float $amountCoins, string $paymentPhone, string $paymentMethod = 'mobile_money'): CoinTransaction
    {
        // Charger le wallet
        $user->load('wallet');

        if (!$user->wallet) {
            throw new \Exception('Wallet introuvable pour cet utilisateur.');
        }

        // Vérifications
        if ($amountCoins < self::MIN_WITHDRAWAL_COINS) {
            throw new \Exception('Le montant minimum de retrait est ' . self::MIN_WITHDRAWAL_COINS . ' pièces (' . (self::MIN_WITHDRAWAL_COINS * self::COIN_TO_MONEY_RATE) . ' FCFA)');
        }

        if ($user->wallet->balance < $amountCoins) {
            throw new \Exception('Solde insuffisant. Vous avez ' . $user->wallet->balance . ' pièces.');
        }

        // Vérifier qu'il n'y a pas déjà un retrait en pending
        $hasPendingWithdrawal = CoinTransaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingWithdrawal) {
            throw new \Exception('Vous avez déjà une demande de retrait en attente de traitement.');
        }

        // Calculer les montants
        $amounts = $this->calculateWithdrawalAmounts($amountCoins);

        // Créer la transaction
        $transaction = DB::transaction(function () use ($user, $amounts, $paymentPhone, $paymentMethod) {
            return CoinTransaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount_coins' => $amounts['amount_coins'],
                'amount_money' => $amounts['amount_money'],
                'fee_percentage' => $amounts['fee_percentage'],
                'fee_amount' => $amounts['fee_amount'],
                'net_amount' => $amounts['net_amount'],
                'currency' => self::CURRENCY,
                'payment_phone' => $paymentPhone,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
            ]);
        });

        Log::info("Withdrawal requested by user {$user->id}: {$amounts['amount_coins']} coins ({$amounts['net_amount']} FCFA) to {$paymentPhone}");

        // Notifier admins et moderators
        $this->notifyAdminsOfWithdrawal($transaction);

        // Envoyer email au user
        Mail::to($user)->send(new WithdrawalRequestedMail($user, $transaction));

        return $transaction;
    }

    /**
     * Approuver un retrait (admin/moderator)
     */
    public function approveWithdrawal(CoinTransaction $transaction, User $admin, ?string $adminNote = null): void
    {
        if ($transaction->type !== 'withdrawal') {
            throw new \Exception('Cette transaction n\'est pas un retrait');
        }

        if ($transaction->status !== 'pending') {
            throw new \Exception('Cette transaction n\'est pas en attente');
        }

        DB::transaction(function () use ($transaction, $admin) {
            $user = $transaction->user;
            $user->load('wallet');

            if (!$user->wallet) {
                throw new \Exception('Wallet introuvable pour cet utilisateur');
            }

            // Vérifier encore le solde
            if ($user->wallet->balance < $transaction->amount_coins) {
                throw new \Exception('L\'utilisateur n\'a plus assez de pièces');
            }

            // Débiter le wallet
            $user->wallet->decrement('balance', $transaction->amount_coins);

            // Marquer comme complété
            $transaction->update([
                'status' => 'completed',
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'admin_note' => $adminNote,
            ]);

            Log::info("Withdrawal approved by admin {$admin->id}: {$transaction->amount_coins} coins removed from user {$user->id}");
        });

        // Envoyer email au user
        Mail::to($transaction->user)->send(new WithdrawalCompletedMail($transaction->user, $transaction));
    }

    /**
     * Rejeter un retrait (admin/moderator)
     */
    public function rejectWithdrawal(CoinTransaction $transaction, User $admin, string $reason): void
    {
        if ($transaction->type !== 'withdrawal') {
            throw new \Exception('Cette transaction n\'est pas un retrait');
        }

        if ($transaction->status !== 'pending') {
            throw new \Exception('Cette transaction n\'est pas en attente');
        }

        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        Log::info("Withdrawal rejected by admin {$admin->id} for transaction {$transaction->uuid}. Reason: {$reason}");

        // Envoyer email au user
        Mail::to($transaction->user)->send(new WithdrawalRejectedMail($transaction->user, $transaction));
    }

    /**
     * Notifier les admins et moderators d'une nouvelle demande de retrait
     */
    protected function notifyAdminsOfWithdrawal(CoinTransaction $transaction): void
    {
        $adminsAndModerators = User::whereIn('role', ['admin', 'moderator'])->get();

        foreach ($adminsAndModerators as $admin) {
            Mail::to($admin)->send(new WithdrawalRequestAdminMail($transaction->user, $transaction));
        }

        Log::info("Withdrawal request notifications sent to {$adminsAndModerators->count()} admins/moderators");
    }

    /**
     * Obtenir l'historique des transactions d'un utilisateur
     */
    public function getUserTransactions(User $user, ?string $type = null)
    {
        $query = CoinTransaction::where('user_id', $user->id)
            ->with('processor:id,name')
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Obtenir toutes les demandes de retrait en attente (pour admins)
     */
    public function getPendingWithdrawals()
    {
        return CoinTransaction::withdrawals()
            ->pending()
            ->with('user:id,name,email')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
