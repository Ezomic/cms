<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Mail\LoginCodeMail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SendLoginCode
{
    public const VALID_MINUTES = 10;

    private const RESEND_AFTER_SECONDS = 60;

    public function handle(User $user): void
    {
        if ($this->issuedRecently($user)) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'login_code_hash' => Hash::make($code),
            'login_code_expires_at' => CarbonImmutable::now()->addMinutes(self::VALID_MINUTES),
            'login_code_attempts' => 0,
        ])->save();

        Mail::to($user->email)->queue(new LoginCodeMail($code));
    }

    /**
     * Replacing a code that is still fresh would let a request flood invalidate
     * the one the admin is about to type. The issue time is derived from the
     * expiry, so no extra column is needed.
     */
    private function issuedRecently(User $user): bool
    {
        if (! $user->login_code_hash || ! $user->login_code_expires_at) {
            return false;
        }

        $issuedAt = $user->login_code_expires_at->toImmutable()->subMinutes(self::VALID_MINUTES);

        return $issuedAt->addSeconds(self::RESEND_AFTER_SECONDS)->isFuture();
    }
}
