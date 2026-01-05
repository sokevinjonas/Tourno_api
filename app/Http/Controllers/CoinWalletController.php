<?php

namespace App\Http\Controllers;

use App\Models\CoinTransaction;
use App\Services\CoinWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CoinWalletController extends Controller
{
    public function __construct(
        protected CoinWalletService $coinWalletService
    ) {}

    /**
     * Obtenir le solde de pièces de l'utilisateur
     */
    public function getBalance(Request $request)
    {
        $user = $request->user();
        $user->load('wallet');

        return response()->json([
            'success' => true,
            'balance' => $user->wallet->balance ?? 0,
        ]);
    }

    /**
     * Initier un dépôt de pièces
     */
    public function initiateDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount_money' => 'required|numeric|min:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->coinWalletService->initiateDeposit(
                $request->user(),
                $request->amount_money
            );

            return response()->json([
                'success' => true,
                'message' => 'Dépôt initié avec succès',
                'data' => [
                    'transaction' => $result['transaction'],
                    'payment_url' => $result['payment_url'],
                    'token' => $result['token'],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Demander un retrait de pièces
     */
    public function requestWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount_coins' => 'required|numeric|min:5',
            'payment_phone' => 'required|string|max:20',
            'payment_method' => 'required|in:orange_money,mtn_money,moov_money,wave',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $transaction = $this->coinWalletService->requestWithdrawal(
                $request->user(),
                $request->amount_coins,
                $request->payment_phone,
                $request->payment_method
            );

            return response()->json([
                'success' => true,
                'message' => 'Demande de retrait enregistrée avec succès',
                'data' => $transaction,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtenir l'historique des transactions de l'utilisateur
     */
    public function getTransactions(Request $request)
    {
        $type = $request->query('type'); // 'deposit' ou 'withdrawal'
        $status = $request->query('status'); // 'pending', 'completed', etc.

        $transactions = $this->coinWalletService->getUserTransactions(
            $request->user(),
            $type
        );

        if ($status) {
            $transactions = $transactions->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Obtenir une transaction spécifique
     */
    public function getTransaction(Request $request, $uuid)
    {
        $transaction = CoinTransaction::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction introuvable',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * ADMIN: Obtenir toutes les demandes de retrait en attente
     */
    public function getPendingWithdrawals(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux admins et moderators.',
            ], 403);
        }

        $withdrawals = CoinTransaction::withdrawals()
            ->pending()
            ->with('user:id,name,email,phone')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }

    /**
     * ADMIN: Obtenir tous les dépôts (pour monitoring)
     */
    public function getAllDeposits(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux admins et moderators.',
            ], 403);
        }

        $status = $request->query('status');
        $limit = $request->query('limit', 50);

        $query = CoinTransaction::deposits()
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($status) {
            $query->where('status', $status);
        }

        $deposits = $query->get();

        return response()->json([
            'success' => true,
            'data' => $deposits,
        ]);
    }

    /**
     * ADMIN: Obtenir tous les retraits (pour monitoring)
     */
    public function getAllWithdrawals(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux admins et moderators.',
            ], 403);
        }

        $status = $request->query('status');
        $limit = $request->query('limit', 50);

        $query = CoinTransaction::withdrawals()
            ->with(['user:id,name,email,phone', 'processor:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($status) {
            $query->where('status', $status);
        }

        $withdrawals = $query->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }

    /**
     * ADMIN: Obtenir toutes les transactions (dépôts + retraits)
     */
    public function getAllTransactions(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux admins et moderators.',
            ], 403);
        }

        $type = $request->query('type'); // 'deposit' ou 'withdrawal'
        $status = $request->query('status');
        $limit = $request->query('limit', 100);

        $query = CoinTransaction::with(['user:id,name,email', 'processor:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * ADMIN: Approuver un retrait
     */
    public function approveWithdrawal(Request $request, $uuid)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux admins et moderators.',
            ], 403);
        }

        $transaction = CoinTransaction::where('uuid', $uuid)->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction introuvable',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'admin_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->coinWalletService->approveWithdrawal(
                $transaction,
                $user,
                $request->admin_note
            );

            return response()->json([
                'success' => true,
                'message' => 'Retrait approuvé avec succès',
                'data' => $transaction->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * ADMIN: Rejeter un retrait
     */
    public function rejectWithdrawal(Request $request, $uuid)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux admins et moderators.',
            ], 403);
        }

        $transaction = CoinTransaction::where('uuid', $uuid)->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction introuvable',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->coinWalletService->rejectWithdrawal(
                $transaction,
                $user,
                $request->rejection_reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Retrait rejeté',
                'data' => $transaction->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
