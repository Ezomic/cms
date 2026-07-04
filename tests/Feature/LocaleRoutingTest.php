<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dutch_routes_resolve_and_set_locale(): void
    {
        $project = Project::create([
            'name' => 'Acme Rebuild',
            'tags' => 'Laravel',
            'body' => '<p>Case study.</p>',
            'published' => true,
            'sort_order' => 0,
        ]);

        foreach (['/nl', '/nl/work', '/nl/work/tag/Laravel', "/nl/work/{$project->slug}", '/nl/docs'] as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertSee('<html lang="nl">', false);
        }
    }

    public function test_root_routes_stay_english(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<html lang="en">', false);
    }

    public function test_hreflang_alternates_point_to_real_locale_urls(): void
    {
        $response = $this->get('/work');

        $response->assertSee('<link rel="alternate" hreflang="en" href="'.route('work.index').'">', false);
        $response->assertSee('<link rel="alternate" hreflang="nl" href="'.route('nl.work.index').'">', false);
        $response->assertSee('<link rel="alternate" hreflang="x-default" href="'.route('work.index').'">', false);
    }

    public function test_dutch_page_canonical_is_the_dutch_url(): void
    {
        $response = $this->get('/nl/work');

        $response->assertSee('<link rel="canonical" href="'.route('nl.work.index').'">', false);
        $response->assertSee('<meta property="og:locale" content="nl_NL">', false);
    }

    public function test_locale_switcher_links_both_directions(): void
    {
        $this->get('/')->assertSee('href="'.route('nl.home').'"', false);
        $this->get('/nl')->assertSee('href="'.route('home').'"', false);
    }

    public function test_alternate_locale_url_regenerates_bound_route_parameters(): void
    {
        $project = Project::create([
            'name' => 'Acme Rebuild',
            'body' => '<p>Case study.</p>',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get("/nl/work/{$project->slug}");

        $response->assertStatus(200);
        $response->assertSee('<link rel="alternate" hreflang="en" href="'.route('project.show', $project->slug).'">', false);
    }

    public function test_session_locale_switch_route_is_removed(): void
    {
        $this->post('/locale/nl')->assertStatus(405);
    }

    public function test_unpublished_project_is_404_under_dutch_prefix(): void
    {
        $project = Project::create(['name' => 'Hidden', 'published' => false, 'sort_order' => 0]);

        $this->get("/nl/work/{$project->slug}")->assertStatus(404);
    }

    public function test_unknown_dutch_url_renders_404_in_dutch(): void
    {
        $response = $this->get('/nl/bestaat-niet');

        $response->assertStatus(404);
        $response->assertSee('Pagina niet gevonden.');
    }
}
