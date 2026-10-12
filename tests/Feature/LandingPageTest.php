<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private function proofProjects(): void
    {
        foreach (['bluevi' => 'BlueVi', 'data-exchange-portal' => 'Data-Exchange Portal', 'bioscape' => 'Bioscape'] as $slug => $name) {
            Project::create([
                'name' => $name,
                'slug' => $slug,
                'outcome' => "English outcome of {$name}.",
                'outcome_nl' => "Nederlandse uitkomst van {$name}.",
                'published' => true,
                'sort_order' => 0,
            ]);
        }
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

    public function test_english_landing_page_renders_with_its_own_meta(): void
    {
        Profile::current()->update(['name' => 'Robbin Thijssen']);

        $response = $this->get('/laravel-developer-enschede');

        $response->assertOk();
        $response->assertSee('<html lang="en">', false);
        $response->assertSee('<title>Freelance Laravel developer in Enschede | Robbin Thijssen</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('landing.enschede').'">', false);
        $response->assertSee('<meta property="og:image" content="'.route('og.home').'">', false);
        $response->assertSee('<h1>A freelance Laravel developer in Enschede.</h1>', false);
        $response->assertSee('Hengelo');
        $response->assertSee('Almelo');
        $response->assertSee('Twente');
    }

    public function test_dutch_landing_page_renders_in_dutch_under_its_own_slug(): void
    {
        $response = $this->get('/nl/laravel-ontwikkelaar-enschede');

        $response->assertOk();
        $response->assertSee('<html lang="nl">', false);
        $response->assertSee('<title>Freelance Laravel-ontwikkelaar Enschede | Robbin Thijssen</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('nl.landing.enschede').'">', false);
        $response->assertSee('<meta property="og:locale" content="nl_NL">', false);
        $response->assertSee('Freelance Laravel-ontwikkelaar in Enschede.');
    }

    public function test_each_locale_does_not_answer_on_the_other_locales_slug(): void
    {
        $this->get('/nl/laravel-developer-enschede')->assertNotFound();
        $this->get('/laravel-ontwikkelaar-enschede')->assertNotFound();
    }

    public function test_the_two_pages_point_at_each_other_with_hreflang(): void
    {
        foreach (['/laravel-developer-enschede', '/nl/laravel-ontwikkelaar-enschede'] as $path) {
            $response = $this->get($path);

            $response->assertSee('<link rel="alternate" hreflang="en" href="'.route('landing.enschede').'">', false);
            $response->assertSee('<link rel="alternate" hreflang="nl" href="'.route('nl.landing.enschede').'">', false);
            $response->assertSee('<link rel="alternate" hreflang="x-default" href="'.route('landing.enschede').'">', false);
        }
    }

    public function test_the_language_toggle_switches_between_the_two_slugs(): void
    {
        $this->get('/laravel-developer-enschede')->assertSee('href="'.route('nl.landing.enschede').'"', false);
        $this->get('/nl/laravel-ontwikkelaar-enschede')->assertSee('href="'.route('landing.enschede').'"', false);
    }

    public function test_proof_cards_link_to_the_case_studies_with_their_localized_outcome(): void
    {
        $this->proofProjects();

        $this->get('/laravel-developer-enschede')
            ->assertSee('href="'.route('project.show', 'bluevi').'"', false)
            ->assertSee('href="'.route('project.show', 'data-exchange-portal').'"', false)
            ->assertSee('href="'.route('project.show', 'bioscape').'"', false)
            ->assertSee('English outcome of BlueVi.');

        $this->get('/nl/laravel-ontwikkelaar-enschede')
            ->assertSee('href="'.route('nl.project.show', 'bluevi').'"', false)
            ->assertSee('Nederlandse uitkomst van BlueVi.');
    }

    public function test_an_unpublished_proof_project_is_left_out(): void
    {
        $this->proofProjects();
        Project::where('slug', 'bioscape')->update(['published' => false]);

        $this->get('/laravel-developer-enschede')
            ->assertDontSee(route('project.show', 'bioscape'), false)
            ->assertSee(route('project.show', 'bluevi'), false);
    }

    public function test_the_call_to_action_links_to_the_existing_contact_form(): void
    {
        $this->get('/laravel-developer-enschede')->assertSee('href="'.route('home').'#contact"', false);
        $this->get('/nl/laravel-ontwikkelaar-enschede')->assertSee('href="'.route('nl.home').'#contact"', false);
    }

    public function test_home_and_footer_link_to_the_landing_page(): void
    {
        $this->get('/')->assertSee('href="'.route('landing.enschede').'"', false);
        $this->get('/work')->assertSee('href="'.route('landing.enschede').'"', false);
        // Application::setLocale() writes app.locale, so Dutch requests go last.
        $this->get('/nl')->assertSee('href="'.route('nl.landing.enschede').'"', false);
        $this->get('/nl/docs')->assertSee('href="'.route('nl.landing.enschede').'"', false);
    }

    public function test_the_copy_has_no_em_dashes_in_either_language(): void
    {
        foreach (['en', 'nl'] as $locale) {
            $copy = json_encode(trans('landing', [], $locale), JSON_UNESCAPED_UNICODE);
            $this->assertIsString($copy);
            $this->assertStringNotContainsString('—', $copy);
            $this->assertStringNotContainsString('–', $copy);
            $this->assertStringNotContainsString(' -- ', $copy);
        }
    }

    public function test_the_meta_title_fits_in_sixty_characters_and_the_description_in_a_snippet(): void
    {
        foreach (['en', 'nl'] as $locale) {
            $this->assertLessThanOrEqual(60, Str::length((string) trans('landing.meta_title', [], $locale)));
            $this->assertLessThanOrEqual(160, Str::length((string) trans('landing.meta_description', [], $locale)));
        }
    }

    public function test_the_landing_meta_differs_from_the_home_meta(): void
    {
        foreach (['en', 'nl'] as $locale) {
            $migration = file_get_contents(database_path('migrations/2026_10_13_100000_update_profile_copy_for_laravel_developer_enschede.php')) ?: '';

            $this->assertStringNotContainsString("'".trans('landing.meta_title', [], $locale)."'", $migration);
            $this->assertStringNotContainsString("'".trans('landing.meta_description', [], $locale)."'", $migration);
        }
    }

    public function test_both_languages_define_the_same_landing_keys(): void
    {
        $this->assertSame(
            array_keys(trans('landing', [], 'en')),
            array_keys(trans('landing', [], 'nl')),
        );
        $this->assertCount(count(trans('landing.build_items', [], 'en')), trans('landing.build_items', [], 'nl'));
    }

    public function test_the_landing_page_json_ld_is_valid_and_typed(): void
    {
        $blocks = $this->jsonLd($this->get('/laravel-developer-enschede')->getContent() ?: '');

        $this->assertCount(2, $blocks);

        $graph = $blocks[0]['@graph'];
        $this->assertSame('ProfessionalService', $graph[0]['@type']);
        $this->assertSame('WebPage', $graph[1]['@type']);
        $this->assertSame($graph[0]['@id'], $graph[1]['about']['@id']);
        $this->assertSame(
            ['Enschede', 'Hengelo', 'Almelo', 'Twente', 'Netherlands'],
            array_column($graph[0]['areaServed'], 'name'),
        );
        $this->assertCount(5, $graph[0]['hasOfferCatalog']['itemListElement']);
        $this->assertSame('BreadcrumbList', $blocks[1]['@type']);
    }

    public function test_the_dutch_landing_page_json_ld_declares_the_dutch_language(): void
    {
        $blocks = $this->jsonLd($this->get('/nl/laravel-ontwikkelaar-enschede')->getContent() ?: '');

        $this->assertSame('nl', $blocks[0]['@graph'][1]['inLanguage']);
        $this->assertSame(route('nl.landing.enschede'), $blocks[0]['@graph'][0]['url']);
    }

    public function test_the_home_person_json_ld_carries_the_service_area(): void
    {
        $person = $this->jsonLd($this->get('/')->getContent() ?: '')[0];

        $this->assertSame('Person', $person['@type']);
        $this->assertContains('Laravel', $person['knowsAbout']);

        foreach ($person['hasOfferCatalog']['itemListElement'] as $offer) {
            $this->assertSame('Offer', $offer['@type']);
            $this->assertContains('Twente', array_column($offer['areaServed'], 'name'));
            $this->assertContains('Enschede', array_column($offer['itemOffered']['areaServed'], 'name'));
        }
    }
}
