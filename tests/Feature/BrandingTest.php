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

    public function test_organization_schema_has_a_square_logo_and_references_the_person(): void
    {
        $response = $this->get('/');

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);
        $nodes = collect($matches[1])->map(fn (string $json): mixed => json_decode($json, true, flags: JSON_THROW_ON_ERROR));
        $person = $nodes->firstWhere('@type', 'Person');
        $organization = $nodes->firstWhere('@type', 'ProfessionalService');

        $this->assertIsArray($person);
        $this->assertIsArray($organization);
        $this->assertArrayNotHasKey('logo', $person);
        $this->assertSame('Thijssen Software', $organization['name']);
        $this->assertSame('ImageObject', $organization['logo']['@type']);
        $this->assertSame(url('/icon-512.png'), $organization['logo']['url']);
        $this->assertGreaterThanOrEqual(112, $organization['logo']['width']);
        $this->assertSame($person['@id'], $organization['founder']['@id']);
        $this->assertArrayNotHasKey('hasOfferCatalog', $organization);
    }

    public function test_favicon_ico_contains_16_32_and_48_pixel_entries(): void
    {
        $data = file_get_contents(public_path('favicon.ico'));
        $this->assertIsString($data);

        $header = unpack('vreserved/vtype/vcount', $data);
        $this->assertIsArray($header);
        $this->assertSame(1, $header['type']);

        $sizes = [];
        for ($i = 0; $i < $header['count']; $i++) {
            $entry = unpack('Cwidth/Cheight', $data, 6 + 16 * $i);
            $this->assertIsArray($entry);
            $this->assertSame($entry['width'], $entry['height']);
            $sizes[] = $entry['width'];
        }

        $this->assertSame([16, 32, 48], $sizes);
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
