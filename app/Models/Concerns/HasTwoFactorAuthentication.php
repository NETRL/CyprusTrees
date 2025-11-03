<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Crypt;

trait HasTwoFactorAuthentication
{

    public function requiresTwoFactor(): bool
    {
        return config('twofactor.enabled') && $this->hasTwoFactorEnabled();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret);
    }

    public function enableTwoFactor(string $secret, array $recoveryCodes): void
    {
        $this->two_factor_secret = Crypt::encryptString($secret);
        $this->two_factor_recovery_codes = Crypt::encryptString(json_encode($recoveryCodes));
        $this->two_factor_confirmed_at = now();
        $this->save();
    }

    public function disableTwoFactor(): void
    {
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;
        $this->two_factor_confirmed_at = null;
        $this->save();
    }

    public function getDecryptedTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret
            ? Crypt::decryptString($this->two_factor_secret)
            : null;
    }

    public function getRecoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($this->two_factor_recovery_codes);

            $codes = json_decode($decrypted, true);

            return is_array($codes) ? $codes : [];
        } catch (\Throwable $e) {
            // If decryption or json_decode fails, treat as "no codes"
            return [];
        }
    }

    public function regenerateRecoveryCodes(): array
    {
        $recovery = collect(range(1, 8))
            ->map(fn() => bin2hex(random_bytes(5)))
            ->toArray();

        $this->two_factor_recovery_codes = Crypt::encryptString(json_encode($recovery));
        $this->save();

        return $recovery;
    }
}
