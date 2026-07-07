<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PruneOgImageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prunes_stale_og_cache_entries_only(): void
    {
        $cache = Cache::store('database');

        $cache->put('og.home.1000000000', 'stale-home', now()->addYear());
        $cache->put('og.project.1.1000000000', 'stale-project', now()->addYear());
        $cache->put('og.home.'.now()->getTimestamp(), 'fresh-home', now()->addYear());
        $cache->put('home.page.data.en', ['keep' => true], now()->addYear());

        $this->artisan('og:prune-cache', ['--days' => 30])
            ->assertExitCode(0);

        $this->assertNull($cache->get('og.home.1000000000'));
        $this->assertNull($cache->get('og.project.1.1000000000'));
        $this->assertNotNull($cache->get('og.home.'.now()->getTimestamp()));
        $this->assertNotNull($cache->get('home.page.data.en'));
    }

    public function test_it_reports_zero_when_nothing_is_stale(): void
    {
        Cache::store('database')->put('og.home.'.now()->getTimestamp(), 'fresh-home', now()->addYear());

        $this->artisan('og:prune-cache')
            ->expectsOutputToContain('Pruned 0 stale OG image cache entries')
            ->assertExitCode(0);
    }
}
