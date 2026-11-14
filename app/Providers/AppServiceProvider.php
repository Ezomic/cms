<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The per-email limit is what stops a distributed guesser: it counts
        // attempts against one address however many IPs they come from. Send
        // and verify get separate buckets so requesting codes cannot use up
        // the attempts for entering one. The address is lower-cased and hashed
        // so case variants share a bucket and a huge value cannot overflow the
        // cache key column.
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email) ? mb_strtolower(trim($email)) : '';

            return [
                Limit::perMinute(5)->by((string) $request->ip()),
                Limit::perMinute(5)->by('email:'.$request->path().':'.hash('sha256', $email)),
            ];
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('chronos-webhook', function (Request $request) {
            return Limit::perMinute(60)->by((string) $request->ip());
        });
    }
}
