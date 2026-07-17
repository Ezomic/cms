<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FontsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<array{string}>
     */
    public static function publicUrls(): array
    {
        return [
            ['/'],
            ['/work'],
            ['/blog'],
            ['/docs'],
            ['/this-page-does-not-exist'],
        ];
    }

    #[DataProvider('publicUrls')]
    public function test_public_pages_self_host_fonts_without_google_cdn(string $url): void
    {
        $response = $this->get($url);

        $response->assertSee(asset('fonts/fonts.css'), false);
        $response->assertDontSee('fonts.googleapis.com');
        $response->assertDontSee('fonts.gstatic.com');
    }
}
