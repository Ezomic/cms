<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

trait BustsHomeCache
{
    public static function bootBustsHomeCache(): void
    {
        static::saved(fn () => Cache::forget('home.page.data'));
        static::deleted(fn () => Cache::forget('home.page.data'));
    }
}
