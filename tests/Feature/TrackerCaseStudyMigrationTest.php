<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackerCaseStudyMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migrate(): void
    {
        $migration = require database_path('migrations/2026_11_02_100100_expand_tracker_case_study.php');
        $this->assertInstanceOf(Migration::class, $migration);
        $migration->up();
    }

    private function oldTracker(): Project
    {
        return Project::create([
            'name' => 'Tracker',
            'slug' => 'tracker',
            'tags' => 'Laravel, Vue.js',
            'github_url' => 'https://github.com/Ezomic/tracker',
            'published' => true,
            'sort_order' => 4,
            'description' => 'old',
            'body' => '<p>old</p>',
        ]);
    }

    public function test_it_rewrites_the_copy_but_keeps_ordering_tags_and_visibility(): void
    {
        $this->oldTracker();

        $this->migrate();

        $tracker = Project::where('slug', 'tracker')->firstOrFail();
        $this->assertStringContainsStringIgnoringCase('laravel bug tracker', $tracker->name);
        $this->assertStringContainsStringIgnoringCase('laravel bug tracker', (string) $tracker->meta_title);
        $this->assertStringContainsStringIgnoringCase('laravel bug tracker', (string) $tracker->meta_title_nl);
        $this->assertSame(4, $tracker->sort_order);
        $this->assertSame('Laravel, Vue.js', $tracker->tags);
        $this->assertTrue($tracker->published);
    }

    public function test_it_does_nothing_when_the_project_is_absent(): void
    {
        $this->migrate();

        $this->assertSame(0, Project::withTrashed()->count());
    }

    public function test_the_english_page_targets_the_keyword_and_links_the_repo(): void
    {
        $this->oldTracker();
        $this->migrate();

        $response = $this->get('/work/tracker')->assertOk();
        $response->assertSeeInOrder(['<h1>', 'Laravel bug tracker', '</h1>'], false);
        $response->assertSee('self-hosted Linear alternative', false);
        $response->assertSee('href="https://github.com/Ezomic/tracker"', false);
        $this->assertMatchesRegularExpression('/<title>[^<]*Laravel bug tracker[^<]*<\/title>/i', (string) $response->getContent());
    }

    public function test_the_dutch_page_is_translated_and_links_the_repo(): void
    {
        $this->oldTracker();
        $this->migrate();

        $response = $this->get('/nl/work/tracker')->assertOk();
        $response->assertSee('zelf-gehoste', false);
        $response->assertSee('href="https://github.com/Ezomic/tracker"', false);
        $response->assertDontSee('self-hosted Linear alternative', false);
    }

    public function test_the_body_links_to_the_repo_in_both_languages(): void
    {
        $tracker = $this->oldTracker();
        $this->migrate();
        $tracker->refresh();

        $this->assertStringContainsString('https://github.com/Ezomic/tracker', (string) $tracker->body);
        $this->assertStringContainsString('https://github.com/Ezomic/tracker', (string) $tracker->body_nl);
    }
}
