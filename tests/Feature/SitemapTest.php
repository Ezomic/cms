<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_both_locales_with_alternates(): void
    {
        $project = Project::create([
            'name' => 'Acme Rebuild',
            'tags' => 'Laravel',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<loc>'.route('work.index').'</loc>', false);
        $response->assertSee('<loc>'.route('nl.work.index').'</loc>', false);
        $response->assertSee('<loc>'.route('project.show', $project->slug).'</loc>', false);
        $response->assertSee('<loc>'.route('work.tag', 'Laravel').'</loc>', false);
        $response->assertSee('<xhtml:link rel="alternate" hreflang="nl" href="'.route('nl.work.index').'"/>', false);
        $response->assertSee('<xhtml:link rel="alternate" hreflang="x-default" href="'.route('work.index').'"/>', false);
        $response->assertSee('<lastmod>', false);
    }

    public function test_sitemap_excludes_unpublished_projects(): void
    {
        $project = Project::create(['name' => 'Hidden', 'published' => false, 'sort_order' => 0]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertDontSee("/work/{$project->slug}<", false);
    }

    public function test_robots_txt_references_sitemap_and_disallows_admin(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: '.route('sitemap'));
        $response->assertSee('Disallow: /admin');
    }
}
