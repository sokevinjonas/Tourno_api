<?php

namespace App\Http\Controllers;

use App\Services\CoinWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected CoinWalletService $coinWalletService
    ) {}

    /**
     * Traiter le webhook FusionPay
     */
    public function fusionpay(Request $request)
    {
        try {
            // Récupérer les données du webhook
            $payload = $request->all();

            Log::info('FusionPay webhook endpoint hit', [
                'payload' => $payload,
                'headers' => $request->headers->all(),
            ]);

            // Traiter le webhook
            $this->coinWalletService->processFusionPayWebhook($payload);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('FusionPay webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Retourner 200 quand même pour éviter que FusionPay réessaie
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 200);
        }
    }
}
