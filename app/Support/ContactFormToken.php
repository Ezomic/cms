<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Config;

final class ContactFormToken
{
    /**
     * A visitor cannot read the form, write a message and submit inside this
     * window. A script that fetches the page only to post it straight back can.
     */
    private const MIN_DWELL_SECONDS = 3;

    /**
     * Bounds how long a scraped token stays usable, so one page fetch cannot
     * supply a week of submissions.
     */
    private const MAX_AGE_SECONDS = 7200;

    public static function issue(): string
    {
        $issuedAt = (string) now()->getTimestamp();

        return $issuedAt.'.'.self::sign($issuedAt);
    }

    public static function isValid(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        $parts = explode('.', $token, 2);

        if (count($parts) !== 2 || ! ctype_digit($parts[0])) {
            return false;
        }

        [$issuedAt, $signature] = $parts;

        if (! hash_equals(self::sign($issuedAt), $signature)) {
            return false;
        }

        $age = now()->getTimestamp() - (int) $issuedAt;

        return $age >= self::MIN_DWELL_SECONDS && $age <= self::MAX_AGE_SECONDS;
    }

    private static function sign(string $issuedAt): string
    {
        return hash_hmac('sha256', $issuedAt, Config::string('app.key'));
    }
}
