<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_shows_profile_and_content(): void
    {
        Profile::current()->update(['name' => 'Jane Developer', 'tagline' => 'Backend Engineer']);
        Skill::create(['category' => 'Backend', 'name' => 'Laravel', 'sort_order' => 0]);
        Project::create(['name' => 'Acme Rebuild', 'sort_order' => 0]);
        Testimonial::create(['quote' => 'Great to work with.', 'author_name' => 'A Client', 'featured' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Jane Developer');
        $response->assertSee('Laravel');
        $response->assertSee('Acme Rebuild');
        $response->assertSee('Great to work with.');
    }

    public function test_home_page_only_shows_featured_testimonials(): void
    {
        Testimonial::create(['quote' => 'This one should show.', 'author_name' => 'Featured Client', 'featured' => true]);
        Testimonial::create(['quote' => 'This one should stay hidden.', 'author_name' => 'Unfeatured Client', 'featured' => false]);

        $response = $this->get('/');

        $response->assertSee('This one should show.');
        $response->assertDontSee('This one should stay hidden.');
    }

    public function test_footer_hides_kvk_number_when_blank(): void
    {
        Profile::current()->update(['kvk_number' => null]);

        $response = $this->get('/');

        $response->assertDontSee('KVK');
    }

    public function test_footer_shows_kvk_number_when_set(): void
    {
        Profile::current()->update(['kvk_number' => '12345678']);

        $response = $this->get('/');

        $response->assertSee('KVK 12345678');
    }

    public function test_work_year_has_no_dangling_separator_when_client_name_is_blank(): void
    {
        Project::create(['name' => 'No Client Project', 'year' => '2025', 'client_name' => null, 'published' => true, 'sort_order' => 0]);

        $response = $this->get('/');

        $response->assertSee('2025');
        $response->assertDontSee('2025 ·');
    }

    /**
     * The Inertia middleware is scoped to /admin, so an X-Inertia header on a
     * public page must not turn the Blade response into an Inertia one.
     */
    public function test_public_pages_ignore_the_inertia_header(): void
    {
        $response = $this->withHeaders(['X-Inertia' => 'true'])->get('/');

        $response->assertOk();
        $response->assertHeaderMissing('X-Inertia');
        $this->assertStringContainsString('<!DOCTYPE html>', $response->getContent());
    }

    public function test_home_shows_only_featured_projects_when_any_are_featured(): void
    {
        Project::create(['name' => 'Featured One', 'published' => true, 'featured' => true, 'sort_order' => 1]);
        Project::create(['name' => 'Plain One', 'published' => true, 'featured' => false, 'sort_order' => 2]);

        $response = $this->get('/');

        $response->assertSee('Featured One');
        $response->assertDontSee('Plain One');
    }

    /**
     * An install with nothing featured is the default state, and a home page
     * with an empty work section reads as broken rather than unconfigured.
     */
    public function test_home_falls_back_to_all_published_projects_when_none_are_featured(): void
    {
        Project::create(['name' => 'Alpha Project', 'published' => true, 'featured' => false, 'sort_order' => 1]);
        Project::create(['name' => 'Beta Project', 'published' => true, 'featured' => false, 'sort_order' => 2]);

        $response = $this->get('/');

        $response->assertSee('Alpha Project');
        $response->assertSee('Beta Project');
    }

    public function test_featuring_a_project_busts_the_home_cache(): void
    {
        Project::create(['name' => 'Alpha Project', 'published' => true, 'featured' => false, 'sort_order' => 1]);
        $beta = Project::create(['name' => 'Beta Project', 'published' => true, 'featured' => false, 'sort_order' => 2]);

        $this->get('/')->assertSee('Alpha Project');

        $beta->update(['featured' => true]);

        $response = $this->get('/');
        $response->assertSee('Beta Project');
        $response->assertDontSee('Alpha Project');
    }

    public function test_unpublished_projects_are_never_featured_onto_the_home_page(): void
    {
        Project::create(['name' => 'Draft Featured', 'published' => false, 'featured' => true, 'sort_order' => 1]);
        Project::create(['name' => 'Live Plain', 'published' => true, 'featured' => false, 'sort_order' => 2]);

        $response = $this->get('/');

        $response->assertDontSee('Draft Featured');
        $response->assertSee('Live Plain');
    }

    public function test_the_work_archive_ignores_featured(): void
    {
        Project::create(['name' => 'Featured One', 'published' => true, 'featured' => true, 'sort_order' => 1]);
        Project::create(['name' => 'Plain One', 'published' => true, 'featured' => false, 'sort_order' => 2]);

        $response = $this->get('/work');

        $response->assertSee('Featured One');
        $response->assertSee('Plain One');
    }
}
