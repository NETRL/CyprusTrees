<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        // if already enabled, just show status
        if ($user->hasTwoFactorEnabled()) {
            return inertia('Profile/TwoFactor', [
                'enabled' => true,
                'recoveryCodes' => $user->getRecoveryCodes(),
            ]);
        }

        // Reuse existing secret in session if it exists (so refresh doesn't change QR)
        $secret = $request->session()->get('two_factor_secret');

        if (! $secret) {
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('two_factor_secret', $secret);
        }

        // create QR data (otpauth url)
        $otpauthUrl  = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        return inertia('Profile/TwoFactor', [
            'enabled' => false,
            'secret' => $secret,
            'otpauthUrl' => $otpauthUrl,
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $google2fa = new Google2FA();

        // Get secret from session
        $secret = $request->session()->pull('two_factor_secret');

        if (! $secret) {
            return back()->withErrors([
                'code' => 'Setup session expired. Please start again.',
            ]);
        }

        $valid = $google2fa->verifyKey($secret, $request->string('code'));

        if (! $valid) {
            // put secret back so user can retry without restarting flow
            $request->session()->put('two_factor_secret', $secret);

            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $user = $request->user();

        // generate recovery codes
        $recovery = collect(range(1, 8))
            ->map(fn() => bin2hex(random_bytes(5)))
            ->toArray();

        $user->enableTwoFactor($secret, $recovery);

        return back()->with('status', 'two-factor-enabled');
    }


    public function disable(Request $request)
    {
        $request->user()->disableTwoFactor();

        return back()->with('status', 'two-factor-disabled');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        $user->regenerateRecoveryCodes();

        return back()->with('status', 'two-factor-recovery-codes-regenerated');
    }
}
