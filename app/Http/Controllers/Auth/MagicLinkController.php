<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MagicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MagicLinkController extends Controller
{
    protected MagicLinkService $magicLinkService;

    public function __construct(MagicLinkService $magicLinkService)
    {
        $this->magicLinkService = $magicLinkService;
    }

    /**
     * Send authentication code to user's email
     */
    public function sendLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->magicLinkService->sendMagicLink(
                $request->email,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Échec de l\'envoi du code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify authentication code
     */
    public function verifyLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->magicLinkService->verifyMagicLink($request->code);

            return response()->json([
                'message' => 'Authentification réussie',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Échec de l\'authentification',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
