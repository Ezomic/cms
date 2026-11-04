<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerifyLoginCode
{
    private const MAX_FAILURES = 5;

    public function handle(User $user, string $code): bool
    {
        if (! $user->login_code_hash || ! $user->login_code_expires_at) {
            return false;
        }

        if ($user->login_code_expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($code, $user->login_code_hash)) {
            $this->recordFailure($user);

            return false;
        }

        $this->clear($user);

        return true;
    }

    private function recordFailure(User $user): void
    {
        $user->increment('login_code_attempts');

        if ($user->login_code_attempts >= self::MAX_FAILURES) {
            $this->clear($user);
        }
    }

    private function clear(User $user): void
    {
        $user->forceFill([
            'login_code_hash' => null,
            'login_code_expires_at' => null,
            'login_code_attempts' => 0,
        ])->save();
    }
}
