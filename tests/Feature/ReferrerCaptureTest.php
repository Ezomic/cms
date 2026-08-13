<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReferrerCaptureTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    private function visit(string $path = '/', array $headers = []): void
    {
        $this->withHeaders(['User-Agent' => self::BROWSER] + $headers)->get($path)->assertOk();
    }

    public function test_it_records_the_referrer_host(): void
    {
        $this->visit('/', ['referer' => 'https://www.linkedin.com/feed/']);

        $this->assertSame('linkedin.com', PageView::sole()->referrer_host);
    }

    /**
     * The whole privacy position of this feature: a referrer URL can carry
     * search terms, session ids and usernames, and none of that is stored.
     *
     * @return array<string, array{0: string}>
     */
    public static function referrersWithSensitiveTails(): array
    {
        return [
            'search terms' => ['https://www.google.com/search?q=freelance+laravel+developer+amsterdam'],
            'session id' => ['https://example.com/dashboard?session=abc123&user=robbin'],
            'username in path' => ['https://forum.example.com/users/robbin/posts/42'],
            'fragment' => ['https://news.example.com/article#comment-99'],
        ];
    }

    #[DataProvider('referrersWithSensitiveTails')]
    public function test_it_stores_only_the_host(string $referrer): void
    {
        $this->visit('/', ['referer' => $referrer]);

        $stored = (string) PageView::sole()->referrer_host;

        $this->assertStringNotContainsString('/', $stored);
        $this->assertStringNotContainsString('?', $stored);
        $this->assertStringNotContainsString('#', $stored);
        $this->assertStringNotContainsString('robbin', $stored);
        $this->assertStringNotContainsString('freelance', $stored);
    }

    public function test_no_referrer_is_recorded_as_direct(): void
    {
        $this->visit('/');

        $this->assertSame(PageView::DIRECT, PageView::sole()->referrer_host);
    }

    public function test_an_empty_referrer_is_recorded_as_direct(): void
    {
        $this->visit('/', ['referer' => '   ']);

        $this->assertSame(PageView::DIRECT, PageView::sole()->referrer_host);
    }

    public function test_a_malformed_referrer_is_recorded_as_direct(): void
    {
        $this->visit('/', ['referer' => 'not a url at all']);

        $this->assertSame(PageView::DIRECT, PageView::sole()->referrer_host);
    }

    public function test_internal_navigation_counts_as_direct_not_a_traffic_source(): void
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        $this->visit('/work', ['referer' => 'https://'.$host.'/']);

        $this->assertSame(PageView::DIRECT, PageView::sole()->referrer_host);
    }

    public function test_the_www_prefix_is_normalised_away(): void
    {
        $this->visit('/', ['referer' => 'https://www.github.com/someone']);
        $this->visit('/work', ['referer' => 'https://github.com/someone']);

        $this->assertSame(['github.com'], PageView::pluck('referrer_host')->unique()->values()->all());
    }

    public function test_the_host_is_lowercased(): void
    {
        $this->visit('/', ['referer' => 'https://LinkedIn.COM/feed']);

        $this->assertSame('linkedin.com', PageView::sole()->referrer_host);
    }

    public function test_crawler_traffic_records_no_referrer_row_at_all(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'referer' => 'https://news.ycombinator.com/',
        ])->get('/')->assertOk();

        $this->assertSame(0, PageView::count());
    }

    public function test_the_dashboard_lists_top_referrers(): void
    {
        PageView::create(['path' => '/', 'referrer_host' => 'linkedin.com']);
        PageView::create(['path' => '/', 'referrer_host' => 'linkedin.com']);
        PageView::create(['path' => '/work', 'referrer_host' => 'github.com']);

        $this->actingAs(User::factory()->create())->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->where('topReferrers.0.host', 'linkedin.com')
                ->where('topReferrers.0.views', 2)
                ->where('topReferrers.1.host', 'github.com')
                ->where('topReferrers.1.views', 1)
                ->etc()
        );
    }

    public function test_the_dashboard_referrer_panel_ignores_rows_outside_the_window(): void
    {
        PageView::create(['path' => '/', 'referrer_host' => 'fresh.example']);
        PageView::create(['path' => '/', 'referrer_host' => 'stale.example'])
            ->forceFill(['created_at' => now()->subDays(45)])->save();

        $this->actingAs(User::factory()->create())->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->where('topReferrers.0.host', 'fresh.example')
                ->count('topReferrers', 1)
                ->etc()
        );
    }
}
