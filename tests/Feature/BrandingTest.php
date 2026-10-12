<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_link_the_icons_and_theme_color(): void
    {
        foreach (['/', '/work', '/nl'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $response->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml" sizes="any">', false);
            $response->assertSee('<link rel="icon" href="/favicon.ico" sizes="48x48">', false);
            $response->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
            $response->assertSee('<meta name="theme-color" content="#4b3fd0">', false);
        }
    }

    public function test_admin_login_links_the_icons(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml" sizes="any">', false)
            ->assertSee('<meta name="theme-color" content="#4b3fd0">', false);
    }

    public function test_header_shows_the_lockup_with_an_accessible_name(): void
    {
        $this->get('/')->assertSee('<img src="/logo-lockup.svg" alt="Thijssen Software" width="173" height="28">', false);
    }

    public function test_person_schema_has_a_square_logo(): void
    {
        $response = $this->get('/');

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);
        $person = collect($matches[1])->map(fn (string $json): mixed => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
            ->firstWhere('@type', 'Person');

        $this->assertIsArray($person);
        $this->assertSame('ImageObject', $person['logo']['@type']);
        $this->assertSame(url('/icon-512.png'), $person['logo']['url']);
        $this->assertGreaterThanOrEqual(112, $person['logo']['width']);
    }

    public function test_icon_files_exist_with_expected_sizes(): void
    {
        foreach (['apple-touch-icon.png' => 180, 'icon-192.png' => 192, 'icon-512.png' => 512] as $file => $size) {
            $info = getimagesize(public_path($file));
            $this->assertIsArray($info);
            $this->assertSame([$size, $size], [$info[0], $info[1]]);
        }

        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertFileExists(public_path('logo-lockup.svg'));
    }
}
