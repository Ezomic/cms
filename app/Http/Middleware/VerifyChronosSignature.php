<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyChronosSignature
{
    private const TOLERANCE_SECONDS = 300;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->isAuthentic($request), 401);

        return $next($request);
    }

    private function isAuthentic(Request $request): bool
    {
        $secret = config('services.chronos.webhook_secret');
        $timestamp = $request->headers->get('X-Chronos-Timestamp');
        $signature = $request->headers->get('X-Chronos-Signature');

        // An unset secret must fail closed: hash_hmac with an empty key still
        // produces a digest, which anyone could compute.
        if (! is_string($secret) || trim($secret) === '') {
            return false;
        }

        if ($timestamp === null || $signature === null || $signature === '') {
            return false;
        }

        if (! ctype_digit($timestamp) || strlen($timestamp) > 12) {
            return false;
        }

        if (abs(now()->getTimestamp() - (int) $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
