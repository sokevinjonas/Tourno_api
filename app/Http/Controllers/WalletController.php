<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get current user's wallet
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getUserWallet($request->user());

        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found',
            ], 404);
        }

        return response()->json([
            'wallet' => $wallet,
        ], 200);
    }

    /**
     * Get wallet balance
     */
    public function balance(Request $request): JsonResponse
    {
        $balance = $this->walletService->getBalance($request->user());

        return response()->json([
            'balance' => $balance,
        ], 200);
    }

    /**
     * Get transaction history
     */
    public function transactions(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $transactions = $this->walletService->getTransactionHistory(
            $request->user(),
            $limit,
            $offset
        );

        return response()->json([
            'transactions' => $transactions,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => $transactions->count(),
            ],
        ], 200);
    }

    /**
     * Get transaction statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->walletService->getTransactionStatistics($user);

        // Structurer la réponse pour le frontend
        $response = [
            'wallet' => [
                'balance' => $stats['balance'],
                'blocked_balance' => $stats['blocked_balance'],
                'available_balance' => $stats['available_balance'],
            ],
            'transactions' => [
                'total_credited' => $stats['total_credited'],
                'total_debited' => $stats['total_debited'],
                'total_count' => $stats['total_transactions'],
            ],
        ];

        // Ajouter les stats de tournois si disponibles
        if (isset($stats['tournament_stats'])) {
            $response['tournaments'] = $stats['tournament_stats'];
        }

        return response()->json($response, 200);
    }

    /**
     * Admin: Add funds to a user's wallet
     */
    public function addFunds(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized: Only admins can add funds',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $targetUser = User::findOrFail($request->user_id);

            $transaction = $this->walletService->adminAddFunds(
                $targetUser,
                $request->amount,
                $request->user(),
                $request->description
            );

            return response()->json([
                'message' => 'Funds added successfully',
                'transaction' => $transaction,
                'new_balance' => $this->walletService->getBalance($targetUser),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add funds',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
