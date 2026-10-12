<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_sitemap_lists_both_landing_pages_with_alternates_pointing_at_each_other(): void
    {
        $xml = (string) $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('<loc>'.route('landing.enschede').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.route('nl.landing.enschede').'</loc>', $xml);

        foreach ([route('landing.enschede'), route('nl.landing.enschede')] as $loc) {
            $block = $this->urlBlock($xml, $loc);

            $this->assertStringContainsString('hreflang="en" href="'.route('landing.enschede').'"', $block);
            $this->assertStringContainsString('hreflang="nl" href="'.route('nl.landing.enschede').'"', $block);
            $this->assertStringContainsString('hreflang="x-default" href="'.route('landing.enschede').'"', $block);
        }
    }

    public function test_home_lastmod_follows_the_latest_content_it_shows_not_just_the_profile(): void
    {
        $this->stamp('profile', Profile::current()->id, '2026-01-01 10:00:00');
        $project = Project::create(['name' => 'Shown', 'published' => true, 'sort_order' => 0]);
        $this->stamp('projects', $project->id, '2026-03-01 10:00:00');
        $testimonial = Testimonial::create(['author_name' => 'A', 'quote' => 'Good.', 'featured' => true]);
        $this->stamp('testimonials', $testimonial->id, '2026-05-01 10:00:00');
        $skill = Skill::create(['category' => 'Back-end', 'name' => 'Laravel', 'sort_order' => 0]);
        $this->stamp('skills', $skill->id, '2026-04-01 10:00:00');

        $this->assertSame('2026-05-01', $this->lastmod(route('home')));
        $this->assertSame('2026-05-01', $this->lastmod(route('nl.home')));

        $this->stamp('skills', $skill->id, '2026-06-01 10:00:00');

        $this->assertSame('2026-06-01', $this->lastmod(route('home')));
    }

    public function test_home_lastmod_ignores_unfeatured_testimonials_and_unpublished_projects(): void
    {
        $this->stamp('profile', Profile::current()->id, '2026-01-01 10:00:00');
        $hidden = Project::create(['name' => 'Hidden', 'published' => false, 'sort_order' => 0]);
        $this->stamp('projects', $hidden->id, '2026-09-01 10:00:00');
        $quiet = Testimonial::create(['author_name' => 'A', 'quote' => 'Good.', 'featured' => false]);
        $this->stamp('testimonials', $quiet->id, '2026-09-01 10:00:00');

        $this->assertSame('2026-01-01', $this->lastmod(route('home')));
    }

    public function test_home_lastmod_only_counts_featured_projects_when_some_are_featured(): void
    {
        $this->stamp('profile', Profile::current()->id, '2026-01-01 10:00:00');
        $featured = Project::create(['name' => 'Featured', 'published' => true, 'featured' => true, 'sort_order' => 0]);
        $other = Project::create(['name' => 'Other', 'published' => true, 'featured' => false, 'sort_order' => 1]);
        $this->stamp('projects', $featured->id, '2026-02-01 10:00:00');
        $this->stamp('projects', $other->id, '2026-08-01 10:00:00');

        $this->assertSame('2026-02-01', $this->lastmod(route('home')));
        $this->assertSame('2026-08-01', $this->lastmod(route('work.index')));
    }

    public function test_work_tag_and_project_lastmod_come_from_their_own_projects(): void
    {
        $laravel = Project::create(['name' => 'Laravel one', 'tags' => 'Laravel', 'published' => true, 'sort_order' => 0]);
        $vue = Project::create(['name' => 'Vue one', 'tags' => 'Vue', 'published' => true, 'sort_order' => 1]);
        $this->stamp('projects', $laravel->id, '2026-02-01 10:00:00');
        $this->stamp('projects', $vue->id, '2026-07-01 10:00:00');

        $this->assertSame('2026-02-01', $this->lastmod(route('work.tag', 'Laravel')));
        $this->assertSame('2026-07-01', $this->lastmod(route('work.tag', 'Vue')));
        $this->assertSame('2026-02-01', $this->lastmod(route('project.show', $laravel->slug)));
    }

    public function test_docs_lastmod_includes_skills_and_projects(): void
    {
        $this->stamp('profile', Profile::current()->id, '2026-01-01 10:00:00');
        $skill = Skill::create(['category' => 'Back-end', 'name' => 'Laravel', 'sort_order' => 0]);
        $this->stamp('skills', $skill->id, '2026-04-01 10:00:00');

        $this->assertSame('2026-04-01', $this->lastmod(route('docs')));
    }

    public function test_landing_lastmod_is_the_newer_of_the_copy_date_and_its_proof_projects(): void
    {
        $this->stamp('profile', Profile::current()->id, '2026-01-01 10:00:00');
        $this->assertSame('2026-10-13', $this->lastmod(route('landing.enschede')));

        $project = Project::create(['name' => 'BlueVi', 'slug' => 'bluevi', 'published' => true, 'sort_order' => 0]);
        $this->stamp('projects', $project->id, '2027-02-01 10:00:00');

        $this->assertSame('2027-02-01', $this->lastmod(route('landing.enschede')));
        $this->assertSame('2027-02-01', $this->lastmod(route('nl.landing.enschede')));
    }

    private function stamp(string $table, int $id, string $updatedAt): void
    {
        DB::table($table)->where('id', $id)->update(['updated_at' => $updatedAt]);
    }

    private function urlBlock(string $xml, string $loc): string
    {
        $this->assertSame(1, preg_match('#<url>\s*<loc>'.preg_quote($loc, '#').'</loc>.*?</url>#s', $xml, $match), "No <url> block for {$loc}");

        return $match[0];
    }

    private function lastmod(string $loc): ?string
    {
        $block = $this->urlBlock((string) $this->get('/sitemap.xml')->getContent(), $loc);

        return preg_match('#<lastmod>(\d{4}-\d{2}-\d{2})#', $block, $match) === 1 ? $match[1] : null;
    }
}
