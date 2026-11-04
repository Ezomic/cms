<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
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

    /**
     * The nonce keeps two visitors who load the form in the same second from
     * holding the same token, which single use would otherwise turn into the
     * second one being dropped.
     */
    public static function issue(): string
    {
        $payload = now()->getTimestamp().'.'.bin2hex(random_bytes(8));

        return $payload.'.'.self::sign($payload);
    }

    /**
     * Valid and not seen before. The signature is remembered until the token
     * would have expired anyway, so a captured token cannot be posted twice.
     */
    public static function consume(?string $token): bool
    {
        $age = self::ageOf($token);

        if ($age === null || $token === null) {
            return false;
        }

        $signature = substr($token, (int) strrpos($token, '.') + 1);

        return Cache::add('contact-form-token:'.$signature, true, self::MAX_AGE_SECONDS - $age + 1);
    }

    private static function ageOf(?string $token): ?int
    {
        if ($token === null) {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3 || ! ctype_digit($parts[0]) || ! ctype_xdigit($parts[1])) {
            return null;
        }

        [$issuedAt, $nonce, $signature] = $parts;

        if (! hash_equals(self::sign($issuedAt.'.'.$nonce), $signature)) {
            return null;
        }

        $age = now()->getTimestamp() - (int) $issuedAt;

        return $age >= self::MIN_DWELL_SECONDS && $age <= self::MAX_AGE_SECONDS ? $age : null;
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, Config::string('app.key'));
    }
}
