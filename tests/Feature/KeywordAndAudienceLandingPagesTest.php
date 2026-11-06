<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KeywordAndAudienceLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'go' => ['/freelance-golang-developer', '/nl/freelance-go-ontwikkelaar'],
        'python' => ['/freelance-python-developer', '/nl/freelance-python-ontwikkelaar'],
        'dotnet' => ['/freelance-csharp-dotnet-developer', '/nl/freelance-csharp-dotnet-ontwikkelaar'],
        'agencies' => ['/software-developer-for-agencies', '/nl/software-ontwikkelaar-voor-bureaus'],
        'founders' => ['/mvp-developer-for-startups', '/nl/mvp-ontwikkelaar-voor-startups'],
        'smallbusiness' => ['/custom-software-for-small-business', '/nl/maatwerksoftware-voor-mkb'],
        'linux' => ['/linux-server-setup-and-self-hosting', '/nl/linux-servers-en-self-hosting'],
    ];

    private const PRIVATE_NAMES = [
        'github.com/ezomic/infra', 'longhouse', 'tempo-sidecar', 'yt-transcript', 'ob-weekregistratie', 'weekregistratie', 'claude-config',
        'github.com/ezomic/tempo', 'valheim-devkit',
    ];

    /**
     * @return array<string, array{0: string}>
     */
    public static function pages(): array
    {
        return array_map(fn (string $key): array => [$key], array_combine(array_keys(self::PAGES), array_keys(self::PAGES)));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function paths(): array
    {
        $paths = [];

        foreach (self::PAGES as $key => [$en, $nl]) {
            $paths["{$key} en"] = [$en];
            $paths["{$key} nl"] = [$nl];
        }

        return $paths;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(function (string $json): array {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($decoded);

            return $decoded;
        }, $matches[1]);
    }

    #[DataProvider('pages')]
    public function test_english_page_renders_with_its_own_meta(string $key): void
    {
        $response = $this->get(self::PAGES[$key][0]);

        $response->assertOk();
        $response->assertSee('<html lang="en">', false);
        $response->assertSee('<title>'.e(trans("pages/{$key}.meta_title", [], 'en')).'</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route("landing.{$key}").'">', false);
        $response->assertSee('<h1>'.e(trans("pages/{$key}.hero_headline", [], 'en')).'</h1>', false);
    }

    #[DataProvider('pages')]
    public function test_dutch_page_renders_in_dutch_under_its_own_slug(string $key): void
    {
        $response = $this->get(self::PAGES[$key][1]);

        $response->assertOk();
        $response->assertSee('<html lang="nl">', false);
        $response->assertSee('<title>'.e(trans("pages/{$key}.meta_title", [], 'nl')).'</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route("nl.landing.{$key}").'">', false);
        $response->assertSee('<h1>'.e(trans("pages/{$key}.hero_headline", [], 'nl')).'</h1>', false);
    }

    #[DataProvider('pages')]
    public function test_each_locale_does_not_answer_on_the_other_locales_slug(string $key): void
    {
        [$en, $nl] = self::PAGES[$key];

        $this->get('/nl'.$en)->assertNotFound();
        $this->get(Str::after($nl, '/nl'))->assertNotFound();
    }

    #[DataProvider('pages')]
    public function test_the_two_pages_point_at_each_other_with_hreflang_and_the_toggle(string $key): void
    {
        foreach (self::PAGES[$key] as $path) {
            $response = $this->get($path);

            $response->assertSee('<link rel="alternate" hreflang="en" href="'.route("landing.{$key}").'">', false);
            $response->assertSee('<link rel="alternate" hreflang="nl" href="'.route("nl.landing.{$key}").'">', false);
            $response->assertSee('<link rel="alternate" hreflang="x-default" href="'.route("landing.{$key}").'">', false);
        }

        $this->get(self::PAGES[$key][0])->assertSee('href="'.route("nl.landing.{$key}").'"', false);
        $this->get(self::PAGES[$key][1])->assertSee('href="'.route("landing.{$key}").'"', false);
    }

    public function test_titles_descriptions_and_headlines_are_distinct_across_all_landing_pages(): void
    {
        foreach (['en', 'nl'] as $locale) {
            foreach (['meta_title', 'meta_description', 'hero_headline'] as $field) {
                $values = array_map(fn (string $key): string => (string) trans("pages/{$key}.{$field}", [], $locale), array_keys(self::PAGES));
                $values[] = (string) trans("landing.{$field}", [], $locale);
                $values[] = (string) trans("remote.{$field}", [], $locale);

                $this->assertCount(count($values), array_unique($values), "{$locale} {$field} repeats");
            }
        }
    }

    #[DataProvider('pages')]
    public function test_the_meta_title_fits_in_sixty_characters_and_the_description_in_a_snippet(string $key): void
    {
        foreach (['en', 'nl'] as $locale) {
            $this->assertLessThanOrEqual(60, Str::length((string) trans("pages/{$key}.meta_title", [], $locale)), "{$locale} {$key} title");
            $this->assertLessThanOrEqual(160, Str::length((string) trans("pages/{$key}.meta_description", [], $locale)), "{$locale} {$key} description");
        }
    }

    #[DataProvider('pages')]
    public function test_the_copy_has_no_em_dashes_in_either_language(string $key): void
    {
        foreach (['en', 'nl'] as $locale) {
            $copy = json_encode(trans("pages/{$key}", [], $locale), JSON_UNESCAPED_UNICODE);
            $this->assertIsString($copy);
            $this->assertStringNotContainsString('—', $copy);
            $this->assertStringNotContainsString('–', $copy);
            $this->assertStringNotContainsString(' -- ', $copy);
        }
    }

    #[DataProvider('pages')]
    public function test_both_languages_define_the_same_keys(string $key): void
    {
        $this->assertSame(
            array_keys(trans("pages/{$key}", [], 'en')),
            array_keys(trans("pages/{$key}", [], 'nl')),
        );
    }

    #[DataProvider('paths')]
    public function test_the_page_has_the_book_call_button_and_links_to_the_contact_form(string $path): void
    {
        $nl = str_starts_with($path, '/nl');
        $html = (string) $this->get($path)->assertOk()->getContent();

        $this->assertStringContainsString('class="btn pri book-call"', $html);
        $this->assertStringContainsString('href="'.route($nl ? 'nl.home' : 'home').'#contact"', $html);
    }

    #[DataProvider('paths')]
    public function test_the_page_carries_no_pricing_and_names_no_ai_tool(string $path): void
    {
        $html = strtolower((string) $this->get($path)->assertOk()->getContent());

        foreach (['op aanvraag', 'on request', 'tarief', 'vaste prijs', 'day rate', 'fixed price', 'fixed-price', '€'] as $phrase) {
            $this->assertStringNotContainsString($phrase, $html, "{$path} says '{$phrase}'");
        }

        foreach (['claude', 'openai', 'copilot', 'chatgpt'] as $tool) {
            $this->assertStringNotContainsString($tool, $html, "{$path} names '{$tool}'");
        }
    }

    #[DataProvider('paths')]
    public function test_the_page_does_not_leak_private_repositories(string $path): void
    {
        $html = strtolower((string) $this->get($path)->assertOk()->getContent());

        foreach (self::PRIVATE_NAMES as $name) {
            $this->assertStringNotContainsString($name, $html, "{$path} leaks '{$name}'");
        }
    }

    #[DataProvider('pages')]
    public function test_json_ld_is_valid_and_describes_the_page(string $key): void
    {
        $blocks = $this->jsonLd((string) $this->get(self::PAGES[$key][0])->getContent());

        $this->assertCount(3, $blocks);
        $this->assertSame('ProfessionalService', $blocks[0]['@type']);

        $graph = $blocks[1]['@graph'];
        $this->assertSame('Service', $graph[0]['@type']);
        $this->assertSame(route("landing.{$key}"), $graph[0]['url']);
        $this->assertSame(trans("pages/{$key}.service_type", [], 'en'), $graph[0]['serviceType']);
        $this->assertNotSame('Laravel development', $graph[0]['serviceType']);
        $this->assertNotEmpty($graph[0]['hasOfferCatalog']['itemListElement']);
        $this->assertSame('WebPage', $graph[1]['@type']);
        $this->assertSame($graph[0]['@id'], $graph[1]['about']['@id']);
        $this->assertSame($blocks[0]['@id'], $graph[0]['provider']['@id']);
        $this->assertSame('BreadcrumbList', $blocks[2]['@type']);

        $dutch = $this->jsonLd((string) $this->get(self::PAGES[$key][1])->getContent());
        $this->assertSame('nl', $dutch[1]['@graph'][1]['inLanguage']);
        $this->assertSame(route("nl.landing.{$key}"), $dutch[1]['@graph'][0]['url']);
    }

    #[DataProvider('pages')]
    public function test_sitemap_lists_both_pages_with_alternates_pointing_at_each_other(string $key): void
    {
        $xml = (string) $this->get('/sitemap.xml')->getContent();

        foreach ([route("landing.{$key}"), route("nl.landing.{$key}")] as $loc) {
            $this->assertSame(1, preg_match('#<url>\s*<loc>'.preg_quote($loc, '#').'</loc>.*?</url>#s', $xml, $match), "No <url> for {$loc}");
            $this->assertStringContainsString('<lastmod>', $match[0]);
            $this->assertStringContainsString('hreflang="en" href="'.route("landing.{$key}").'"', $match[0]);
            $this->assertStringContainsString('hreflang="nl" href="'.route("nl.landing.{$key}").'"', $match[0]);
            $this->assertStringContainsString('hreflang="x-default" href="'.route("landing.{$key}").'"', $match[0]);
        }
    }

    #[DataProvider('pages')]
    public function test_home_and_footer_link_to_the_page(string $key): void
    {
        $this->get('/')->assertSee('href="'.route("landing.{$key}").'"', false);
        $this->get('/work')->assertSee('href="'.route("landing.{$key}").'"', false);
        // Application::setLocale() writes app.locale, so Dutch requests go last.
        $this->get('/nl')->assertSee('href="'.route("nl.landing.{$key}").'"', false);
        $this->get('/nl/docs')->assertSee('href="'.route("nl.landing.{$key}").'"', false);
    }

    public function test_the_home_page_lists_the_pages_under_a_specialisations_heading_not_in_the_nav(): void
    {
        $english = $this->get('/')->assertSee('Areas and specialisations')->getContent();

        $this->assertSame(1, preg_match('#<nav.*?</nav>#s', (string) $english, $match));

        foreach (array_keys(self::PAGES) as $key) {
            $this->assertStringNotContainsString(route("landing.{$key}"), $match[0]);
        }

        // Application::setLocale() writes app.locale, so Dutch requests go last.
        $this->get('/nl')->assertSee('Werkgebieden en specialisaties');
    }

    public function test_the_go_page_links_its_two_public_repositories(): void
    {
        foreach (self::PAGES['go'] as $path) {
            $this->get($path)
                ->assertSee('href="https://github.com/Ezomic/filebox"', false)
                ->assertSee('href="https://github.com/Ezomic/streambox"', false);
        }
    }

    public function test_the_dotnet_page_links_overdub_and_the_second_public_repository(): void
    {
        foreach (self::PAGES['dotnet'] as $path) {
            $this->get($path)
                ->assertSee('href="https://github.com/Ezomic/overdub"', false)
                ->assertSee('href="https://github.com/Ezomic/valheim-core"', false);
        }
    }

    public function test_the_python_page_describes_its_work_without_a_single_link_to_github(): void
    {
        foreach (self::PAGES['python'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('github.com/Ezomic/', false);
        }
    }

    public function test_the_linux_page_links_filebox_and_streambox_and_names_no_nas_software_or_infra_repo(): void
    {
        foreach (self::PAGES['linux'] as $path) {
            $html = (string) $this->get($path)
                ->assertSee('href="https://github.com/Ezomic/filebox"', false)
                ->assertSee('href="https://github.com/Ezomic/streambox"', false)
                ->getContent();

            $this->assertSame(2, substr_count($html, 'href="https://github.com/Ezomic/'), "{$path} links another repository");

            foreach (['truenas', 'openmediavault', 'github.com/ezomic/infra', 'synology', 'unraid'] as $name) {
                $this->assertStringNotContainsString($name, strtolower($html), "{$path} names '{$name}'");
            }
        }
    }

    public function test_the_linux_page_describes_the_three_examples_the_rescue_angle_and_the_care_after_a_first_job(): void
    {
        $this->get(self::PAGES['linux'][0])
            ->assertSee('NAS')
            ->assertSee('without downtime', false)
            ->assertSee('a first job comes first', false)
            ->assertSee('on site', false);

        // Application::setLocale() writes app.locale, so Dutch requests go last.
        $this->get(self::PAGES['linux'][1])
            ->assertSee('Enschede')
            ->assertSee('NAS')
            ->assertSee('zonder downtime', false)
            ->assertSee('eerst een eerste opdracht', false)
            ->assertSee('vastgelopen', false);
    }

    public function test_the_linux_page_names_only_tools_the_owner_uses(): void
    {
        $copy = strtolower(json_encode(trans('pages/linux', [], 'en'), JSON_UNESCAPED_UNICODE).json_encode(trans('pages/linux', [], 'nl'), JSON_UNESCAPED_UNICODE));

        foreach (['kubernetes', 'terraform', 'proxmox', 'aws', 'azure', 'postgres', 'apache', 'nextcloud', 'plesk', 'cpanel'] as $tool) {
            $this->assertStringNotContainsString($tool, $copy, "linux copy names '{$tool}'");
        }
    }

    public function test_no_two_landing_pages_share_a_slug_in_either_language(): void
    {
        $slugs = array_merge(...array_values(self::PAGES));
        $slugs[] = '/laravel-developer-enschede';
        $slugs[] = '/laravel-developer-remote';

        $this->assertCount(count($slugs), array_unique($slugs));
    }

    public function test_the_linux_slug_is_case_sensitive_and_a_trailing_slash_still_resolves(): void
    {
        $this->get(strtoupper(self::PAGES['linux'][0]))->assertNotFound();
        $this->get('/Nl/linux-servers-en-self-hosting')->assertNotFound();
        $this->get('/nl/Linux-Servers-En-Self-Hosting')->assertNotFound();

        $this->get(self::PAGES['linux'][0].'/')->assertOk();
    }

    public function test_the_linux_page_does_not_lean_on_a_missing_translation_key(): void
    {
        foreach (['en', 'nl'] as $locale) {
            $copy = (string) json_encode(trans('pages/linux', [], $locale));

            $this->assertStringNotContainsString('pages/linux.', $copy);
        }

        foreach (self::PAGES['linux'] as $path) {
            $this->get($path)->assertDontSee('pages/linux.', false);
        }
    }

    public function test_lastmod_is_the_copy_date_of_the_page(): void
    {
        $xml = (string) $this->get('/sitemap.xml')->getContent();

        foreach (array_keys(self::PAGES) as $key) {
            $this->assertSame(1, preg_match('#<loc>'.preg_quote(route("landing.{$key}"), '#').'</loc>\s*<lastmod>(\d{4}-\d{2}-\d{2})#', $xml, $match));
            $this->assertSame('2026-11-06', $match[1]);
        }
    }
}
