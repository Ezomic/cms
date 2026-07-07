<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

trait BustsHomeCache
{
    public static function bootBustsHomeCache(): void
    {
        static::saved(fn () => static::forgetHomeCache());
        static::deleted(fn () => static::forgetHomeCache());
    }

    protected static function forgetHomeCache(): void
    {
        Cache::forget('home.page.data.en');
        Cache::forget('home.page.data.nl');
    }
}
