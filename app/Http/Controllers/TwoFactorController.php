<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA — generate secret & return QR code URL.
     * POST /api/two-factor/enable
     */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        // Generate secret key
        $secret = $this->google2fa->generateSecretKey();

        // Save secret (not yet confirmed)
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ]);

        // Generate QR code as data URI (SVG)
        $qrCodeUrl = $this->generateQrCodeDataUri($user->email, $secret);

        return response()->json([
            'qr_code_url' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Verify 2FA code & activate.
     * POST /api/two-factor/verify
     */
    public function verify(Request $request): JsonResponse
    {

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => '2FA has not been set up yet.',
            ], 400);
        }

        $secret = decrypt($user->two_factor_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code. Please try again.',
            ], 422);
        }
        $isFirstTime = !$user->two_factor_confirmed_at;
        if ($isFirstTime) {
            $user->update([
                'two_factor_enabled' => true,
                'two_factor_confirmed_at' => now(),
            ]);
        }

        $user->update([
            'two_factor_verified' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => $isFirstTime ? '2FA has been enabled.' : '2FA code verified successfully.'
        ]);
    }

    /**
     * Disable 2FA.
     * POST /api/two-factor/disable
     */
    public function disable(Request $request): JsonResponse
    {

        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is not enabled.',
            ], 400);
        }

        $secret = decrypt($user->two_factor_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return response()->json([
            'message' => '2FA has been disabled.',
        ]);
    }

    /**
     * Generate QR code as base64 data URI.
     */
    private function generateQrCodeDataUri(string $email, string $secret): string
    {
        $appName = config('app.name', 'Synthera');

        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($otpauthUrl);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
