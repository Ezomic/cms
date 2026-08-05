<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageViewRecordingTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    /**
     * Every public page records a view under its own path. Kept as one test
     * over the whole route list so a newly added public page that forgets to
     * record is caught here rather than silently missing from the dashboard.
     *
     * @return array<string, array{0: string}>
     */
    public static function publicPaths(): array
    {
        return [
            'home' => ['/'],
            'home nl' => ['/nl'],
            'docs' => ['/docs'],
            'docs nl' => ['/nl/docs'],
            'work' => ['/work'],
            'work nl' => ['/nl/work'],
        ];
    }

    #[DataProvider('publicPaths')]
    public function test_public_pages_record_a_page_view(string $path): void
    {
        $this->withHeaders(['User-Agent' => self::BROWSER])->get($path)->assertOk();

        $this->assertSame(1, PageView::where('path', $path)->count());
    }

    public function test_project_and_tag_pages_record_a_page_view(): void
    {
        Project::create(['name' => 'Acme Site', 'tags' => 'laravel', 'published' => true, 'sort_order' => 0]);

        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/work/tag/laravel')->assertOk();
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/work/acme-site')->assertOk();

        $this->assertSame(1, PageView::where('path', '/work/tag/laravel')->count());
        $this->assertSame(1, PageView::where('path', '/work/acme-site')->count());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function crawlerAgents(): array
    {
        return [
            'googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
            'bingbot' => ['Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'],
            'ahrefs' => ['Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)'],
            'curl' => ['curl/8.4.0'],
            'wget' => ['Wget/1.21.3'],
            'python requests' => ['python-requests/2.31.0'],
            'headless chrome' => ['Mozilla/5.0 (X11; Linux x86_64) HeadlessChrome/126.0.0.0'],
            'facebook link preview' => ['facebookexternalhit/1.1'],
            'uptime monitor' => ['Better Uptime Bot'],
            'spaced bot name' => ['Some Weird Search Bot/1.0'],
        ];
    }

    #[DataProvider('crawlerAgents')]
    public function test_crawler_traffic_is_not_recorded(string $userAgent): void
    {
        $this->withHeaders(['User-Agent' => $userAgent])->get('/')->assertOk();

        $this->assertSame(0, PageView::count());
    }

    public function test_a_request_without_a_user_agent_header_is_not_recorded(): void
    {
        // Laravel's test client always sends a default agent, so the header is
        // removed at the server-variable level to model a real headerless hit.
        $this->call('GET', '/', server: ['HTTP_USER_AGENT' => null])->assertOk();

        $this->assertSame(0, PageView::count());
    }

    public function test_an_empty_user_agent_is_not_recorded(): void
    {
        $this->withHeaders(['User-Agent' => '  '])->get('/')->assertOk();

        $this->assertSame(0, PageView::count());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function browserAgents(): array
    {
        return [
            'chrome' => [self::BROWSER],
            'safari ios' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1'],
            'firefox' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0'],
        ];
    }

    #[DataProvider('browserAgents')]
    public function test_real_browsers_are_still_recorded(string $userAgent): void
    {
        $this->withHeaders(['User-Agent' => $userAgent])->get('/')->assertOk();

        $this->assertSame(1, PageView::count());
    }
}
