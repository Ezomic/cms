<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RemoteLandingPageTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_english_page_renders_with_its_own_meta(): void
    {
        $response = $this->get('/laravel-developer-remote');

        $response->assertOk();
        $response->assertSee('<html lang="en">', false);
        $response->assertSee('<title>'.trans('remote.meta_title', [], 'en').'</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('landing.remote').'">', false);
        $response->assertSee('<h1>'.trans('remote.hero_headline', [], 'en').'</h1>', false);
        $response->assertSee('Belgium');
        $response->assertSee('Enschede');
    }

    public function test_dutch_page_renders_in_dutch_under_its_own_slug(): void
    {
        $response = $this->get('/nl/laravel-ontwikkelaar-remote');

        $response->assertOk();
        $response->assertSee('<html lang="nl">', false);
        $response->assertSee('<title>'.trans('remote.meta_title', [], 'nl').'</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('nl.landing.remote').'">', false);
        $response->assertSee('<h1>'.trans('remote.hero_headline', [], 'nl').'</h1>', false);
        $response->assertSee('België');
        $response->assertSee('Enschede');
    }

    public function test_each_locale_does_not_answer_on_the_other_locales_slug(): void
    {
        $this->get('/nl/laravel-developer-remote')->assertNotFound();
        $this->get('/laravel-ontwikkelaar-remote')->assertNotFound();
    }

    public function test_the_two_pages_point_at_each_other_with_hreflang(): void
    {
        foreach (['/laravel-developer-remote', '/nl/laravel-ontwikkelaar-remote'] as $path) {
            $response = $this->get($path);

            $response->assertSee('<link rel="alternate" hreflang="en" href="'.route('landing.remote').'">', false);
            $response->assertSee('<link rel="alternate" hreflang="nl" href="'.route('nl.landing.remote').'">', false);
            $response->assertSee('<link rel="alternate" hreflang="x-default" href="'.route('landing.remote').'">', false);
        }
    }

    public function test_the_language_toggle_switches_between_the_two_slugs(): void
    {
        $this->get('/laravel-developer-remote')->assertSee('href="'.route('nl.landing.remote').'"', false);
        $this->get('/nl/laravel-ontwikkelaar-remote')->assertSee('href="'.route('landing.remote').'"', false);
    }

    public function test_the_page_has_distinct_title_description_and_headline_from_the_enschede_page(): void
    {
        foreach (['en', 'nl'] as $locale) {
            foreach (['meta_title', 'meta_description', 'hero_headline'] as $key) {
                $this->assertNotSame(
                    trans('landing.'.$key, [], $locale),
                    trans('remote.'.$key, [], $locale),
                );
            }
        }
    }

    public function test_the_meta_title_fits_in_sixty_characters_and_the_description_in_a_snippet(): void
    {
        foreach (['en', 'nl'] as $locale) {
            $this->assertLessThanOrEqual(60, Str::length((string) trans('remote.meta_title', [], $locale)));
            $this->assertLessThanOrEqual(160, Str::length((string) trans('remote.meta_description', [], $locale)));
        }
    }

    public function test_the_copy_has_no_em_dashes_in_either_language(): void
    {
        foreach (['en', 'nl'] as $locale) {
            $copy = json_encode(trans('remote', [], $locale), JSON_UNESCAPED_UNICODE);
            $this->assertIsString($copy);
            $this->assertStringNotContainsString('—', $copy);
            $this->assertStringNotContainsString('–', $copy);
            $this->assertStringNotContainsString(' -- ', $copy);
        }
    }

    public function test_both_languages_define_the_same_keys(): void
    {
        $this->assertSame(
            array_keys(trans('remote', [], 'en')),
            array_keys(trans('remote', [], 'nl')),
        );
    }

    public function test_the_call_to_action_links_to_the_existing_contact_form(): void
    {
        $this->get('/laravel-developer-remote')->assertSee('href="'.route('home').'#contact"', false);
        $this->get('/nl/laravel-ontwikkelaar-remote')->assertSee('href="'.route('nl.home').'#contact"', false);
    }

    public function test_proof_cards_link_to_case_studies(): void
    {
        foreach (['bluevi' => 'BlueVi', 'data-exchange-portal' => 'Data-Exchange Portal', 'bioscape' => 'Bioscape'] as $slug => $name) {
            Project::create(['name' => $name, 'slug' => $slug, 'published' => true, 'sort_order' => 0]);
        }

        $this->get('/laravel-developer-remote')->assertSee('href="'.route('project.show', 'bluevi').'"', false);
        $this->get('/nl/laravel-ontwikkelaar-remote')->assertSee('href="'.route('nl.project.show', 'bluevi').'"', false);
    }

    public function test_home_and_footer_link_to_the_page(): void
    {
        $this->get('/')->assertSee('href="'.route('landing.remote').'"', false);
        $this->get('/work')->assertSee('href="'.route('landing.remote').'"', false);
        // Application::setLocale() writes app.locale, so Dutch requests go last.
        $this->get('/nl')->assertSee('href="'.route('nl.landing.remote').'"', false);
        $this->get('/nl/docs')->assertSee('href="'.route('nl.landing.remote').'"', false);
    }

    public function test_json_ld_is_valid_and_declares_belgium_as_served(): void
    {
        $blocks = $this->jsonLd($this->get('/laravel-developer-remote')->getContent() ?: '');

        $this->assertCount(3, $blocks);
        $this->assertSame('ProfessionalService', $blocks[0]['@type']);

        $graph = $blocks[1]['@graph'];
        $this->assertSame('Service', $graph[0]['@type']);
        $this->assertSame(route('landing.remote'), $graph[0]['url']);
        $this->assertSame('WebPage', $graph[1]['@type']);
        $this->assertSame($graph[0]['@id'], $graph[1]['about']['@id']);
        $this->assertSame($blocks[0]['@id'], $graph[0]['provider']['@id']);
        $this->assertContains('Belgium', array_column($graph[0]['areaServed'], 'name'));
        $this->assertContains('Netherlands', array_column($graph[0]['areaServed'], 'name'));
        $this->assertSame('BreadcrumbList', $blocks[2]['@type']);
    }

    public function test_the_dutch_json_ld_declares_the_dutch_language(): void
    {
        $blocks = $this->jsonLd($this->get('/nl/laravel-ontwikkelaar-remote')->getContent() ?: '');

        $this->assertSame('nl', $blocks[1]['@graph'][1]['inLanguage']);
        $this->assertSame(route('nl.landing.remote'), $blocks[1]['@graph'][0]['url']);
    }

    public function test_belgium_is_in_the_home_service_area(): void
    {
        $services = array_values(array_filter(
            $this->jsonLd($this->get('/')->getContent() ?: ''),
            fn (array $block): bool => ($block['@type'] ?? null) === 'ProfessionalService',
        ));

        $this->assertContains('Belgium', array_column($services[0]['areaServed'], 'name'));
    }

    public function test_sitemap_lists_both_pages_with_alternates_pointing_at_each_other(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee('<loc>'.route('landing.remote').'</loc>', false);
        $response->assertSee('<loc>'.route('nl.landing.remote').'</loc>', false);
        $response->assertSee('<xhtml:link rel="alternate" hreflang="nl" href="'.route('nl.landing.remote').'"/>', false);
        $response->assertSee('<xhtml:link rel="alternate" hreflang="x-default" href="'.route('landing.remote').'"/>', false);
    }
}
