<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Mail\LoginCodeMail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SendLoginCode
{
    private const RESEND_AFTER_SECONDS = 60;

    /**
     * Issuing a code at most once a minute per user. The marker lives in the
     * cache rather than being derived from the stored code, so clearing a code
     * after too many failures does not let a guesser trade it for a fresh one
     * immediately, and a request flood cannot invalidate the code the admin is
     * about to type.
     */
    public function handle(User $user): void
    {
        if (! Cache::add('login-code-issued:'.$user->id, true, self::RESEND_AFTER_SECONDS)) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'login_code_hash' => Hash::make($code),
            'login_code_expires_at' => CarbonImmutable::now()->addMinutes(10),
            'login_code_attempts' => 0,
        ])->save();

        Mail::to($user->email)->queue(new LoginCodeMail($code));
    }
}
