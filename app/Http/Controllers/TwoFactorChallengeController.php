<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();
        $google2fa = new Google2FA();

        $secret = $user->getDecryptedTwoFactorSecret();
        $code = $request->input('code');
        $valid = $google2fa->verifyKey($secret, $code);

        // allow recovery codes
        if (! $valid) {
            $recoveryCodes = $user->getRecoveryCodes();
            if (in_array($code, $recoveryCodes)) {
                $remaining = array_values(array_diff($recoveryCodes, [$code]));
                $user->two_factor_recovery_codes = encrypt(json_encode($remaining));
                $user->save();
                $valid = true;
            }
        }

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid authentication or recovery code.']);
        }

        // mark this session as passed
        $request->session()->put('2fa_passed', true);

        // redirect to intended or dashboard
        return redirect()->intended('/dashboard');
    }
}
