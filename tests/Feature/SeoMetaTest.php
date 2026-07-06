<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_meta_tags_and_schema(): void
    {
        Profile::current()->update(['name' => 'Jane Developer', 'tagline' => 'Backend Engineer']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<link rel="canonical" href="'.route('home').'">', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:url" content="'.route('home').'">', false);
        $response->assertSee('<meta property="og:locale" content="en_US">', false);
        $response->assertSee('"@type": "Person"', false);
        $response->assertSee('"@type": "WebSite"', false);
    }

    public function test_work_page_has_meta_and_og_tags(): void
    {
        Profile::current()->update(['name' => 'Jane Developer']);

        $response = $this->get('/work');

        $response->assertStatus(200);
        $response->assertSee('<title>Case Studies — Jane Developer</title>', false);
        $response->assertSee('<meta name="description" content="Case studies by Jane Developer.">', false);
        $response->assertSee('<link rel="canonical" href="'.route('work.index').'">', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:image"', false);
    }

    public function test_tag_page_has_breadcrumb_schema(): void
    {
        Project::create(['name' => 'Acme Rebuild', 'tags' => 'Laravel', 'published' => true, 'sort_order' => 0]);

        $response = $this->get('/work/tag/Laravel');

        $response->assertStatus(200);
        $response->assertSee('"@type": "BreadcrumbList"', false);
        $response->assertSee('<link rel="canonical" href="'.route('work.tag', 'Laravel').'">', false);
    }

    public function test_project_page_has_creative_work_and_breadcrumb_schema(): void
    {
        $project = Project::create([
            'name' => 'Acme Rebuild',
            'description' => 'Rebuilt the Acme platform.',
            'tags' => 'Laravel, Vue',
            'published' => true,
            'body' => '<p>Case study body.</p>',
            'sort_order' => 0,
        ]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertStatus(200);
        $response->assertSee('"@type": "CreativeWork"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
        $response->assertSee('<meta name="description" content="Rebuilt the Acme platform.">', false);
        $response->assertSee('<meta property="og:type" content="article">', false);
    }

    public function test_project_meta_fields_override_defaults(): void
    {
        $project = Project::create([
            'name' => 'Acme Rebuild',
            'description' => 'Fallback description.',
            'meta_title' => 'Custom SEO title',
            'meta_description' => 'Custom SEO description.',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertSee('<title>Custom SEO title —', false);
        $response->assertSee('<meta name="description" content="Custom SEO description.">', false);
    }

    public function test_project_meta_description_fallback_chain(): void
    {
        $project = new Project(['name' => 'X', 'meta_description' => 'Meta.', 'description' => 'Desc.', 'body' => '<p>Body.</p>']);
        $this->assertSame('Meta.', $project->metaDescription());

        $project = new Project(['name' => 'X', 'description' => 'Desc.', 'body' => '<p>Body.</p>']);
        $this->assertSame('Desc.', $project->metaDescription());

        $project = new Project(['name' => 'X', 'body' => '<p>Body text here.</p>']);
        $this->assertSame('Body text here.', $project->metaDescription());

        $project = new Project(['name' => 'X']);
        $this->assertSame('', $project->metaDescription());

        $this->assertSame('X', $project->metaTitle());
        $project->meta_title = 'Custom';
        $this->assertSame('Custom', $project->metaTitle());
    }

    public function test_docs_page_has_canonical_and_og_tags(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('<link rel="canonical" href="'.route('docs').'">', false);
        $response->assertSee('<meta property="og:title"', false);
    }

    public function test_work_grid_images_are_lazy_loaded(): void
    {
        Project::create(['name' => 'Acme Rebuild', 'image' => 'projects/x.jpg', 'published' => true, 'sort_order' => 0]);

        $response = $this->get('/work');

        $response->assertSee('loading="lazy" decoding="async"', false);
    }

    public function test_project_cover_is_high_priority(): void
    {
        $project = Project::create(['name' => 'Acme Rebuild', 'image' => 'projects/x.jpg', 'published' => true, 'sort_order' => 0]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertSee('fetchpriority="high"', false);
    }

    public function test_project_page_shows_github_link_when_set(): void
    {
        $project = Project::create([
            'name' => 'Acme Rebuild',
            'github_url' => 'https://github.com/example/acme',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertSee('href="https://github.com/example/acme"', false);
    }

    public function test_project_page_hides_github_link_when_not_set(): void
    {
        $project = Project::create(['name' => 'Acme Rebuild', 'published' => true, 'sort_order' => 0]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertDontSee('<a class="github-link"', false);
    }

    public function test_project_page_shows_gallery_images(): void
    {
        $project = Project::create(['name' => 'Acme Rebuild', 'published' => true, 'sort_order' => 0]);
        $project->images()->create(['path' => 'projects/screen-1.jpg', 'sort_order' => 0]);
        $project->images()->create(['path' => 'projects/screen-2.jpg', 'sort_order' => 1]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertSee('projects/screen-1.jpg', false);
        $response->assertSee('projects/screen-2.jpg', false);
    }

    public function test_project_page_hides_gallery_section_when_no_images(): void
    {
        $project = Project::create(['name' => 'Acme Rebuild', 'published' => true, 'sort_order' => 0]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertDontSee('class="gallery"', false);
    }
}
