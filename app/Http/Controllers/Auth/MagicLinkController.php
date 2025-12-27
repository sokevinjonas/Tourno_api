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
     * Send magic link to user's email
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
                'message' => 'Failed to send magic link',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify magic link token
     */
    public function verifyLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->magicLinkService->verifyMagicLink($request->token);

            return response()->json([
                'message' => 'Authentication successful',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
