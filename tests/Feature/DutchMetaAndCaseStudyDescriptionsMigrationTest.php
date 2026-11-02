<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DutchMetaAndCaseStudyDescriptionsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        $migration = require database_path('migrations/2026_11_02_100000_rewrite_dutch_home_meta_and_add_case_study_descriptions.php');
        $this->assertInstanceOf(Migration::class, $migration);

        return $migration;
    }

    private function liveProfile(): Profile
    {
        $profile = Profile::current();
        $profile->update([
            'meta_title_nl' => 'Laravel-ontwikkelaar in Enschede | Robbin Thijssen',
            'meta_description_nl' => 'Freelance Laravel-ontwikkelaar in Enschede met 9+ jaar ervaring. Laravel- en Vue.js-webapplicaties voor mkb en grote bedrijven in Twente en op afstand.',
        ]);

        return $profile;
    }

    private function project(string $slug): Project
    {
        return Project::create(['name' => $slug, 'slug' => $slug, 'published' => true, 'sort_order' => 0]);
    }

    public function test_the_dutch_home_meta_carries_the_three_search_terms(): void
    {
        $profile = $this->liveProfile();

        $this->migration()->up();
        $profile->refresh();

        $title = mb_strtolower((string) $profile->meta_title_nl);
        $description = mb_strtolower((string) $profile->meta_description_nl);

        $this->assertLessThanOrEqual(60, mb_strlen($title));
        $this->assertLessThanOrEqual(160, mb_strlen($description));

        foreach (['laravel-ontwikkelaar', 'enschede', 'remote'] as $term) {
            $this->assertStringContainsString($term, $title.' '.$description);
        }

        $this->assertStringContainsString('laravel-ontwikkelaar', $title);
        $this->assertStringContainsString('enschede', $title);
    }

    public function test_it_leaves_a_dutch_field_alone_that_was_edited_since(): void
    {
        $profile = $this->liveProfile();
        $profile->update(['meta_title_nl' => 'Een titel die ik zelf schreef']);

        $this->migration()->up();

        $this->assertSame('Een titel die ik zelf schreef', $profile->refresh()->meta_title_nl);
        $this->assertStringContainsString('remote', (string) $profile->meta_description_nl);
    }

    public function test_it_gives_the_data_exchange_portal_its_own_descriptions(): void
    {
        $project = $this->project('data-exchange-portal');

        $this->migration()->up();
        $project->refresh();

        foreach ([$project->meta_description, $project->meta_description_nl] as $description) {
            $this->assertNotEmpty($description);
            $this->assertLessThanOrEqual(160, mb_strlen((string) $description));
        }

        $this->assertNotSame($project->meta_description, $project->meta_description_nl);
        $this->assertStringContainsString('Raet', (string) $project->meta_description);
    }

    public function test_it_does_not_overwrite_a_description_that_is_already_set(): void
    {
        $project = $this->project('data-exchange-portal');
        $project->update(['meta_description' => 'Written by hand.']);

        $this->migration()->up();
        $project->refresh();

        $this->assertSame('Written by hand.', $project->meta_description);
        $this->assertNotEmpty($project->meta_description_nl);
    }

    public function test_it_skips_projects_that_do_not_exist_and_runs_on_an_empty_database(): void
    {
        $this->migration()->up();

        $this->assertSame(0, Project::count());
    }

    public function test_it_is_reversible_and_idempotent(): void
    {
        $profile = $this->liveProfile();
        $project = $this->project('data-exchange-portal');

        $this->migration()->up();
        $this->migration()->up();
        $this->migration()->down();

        $this->assertSame('Laravel-ontwikkelaar in Enschede | Robbin Thijssen', $profile->refresh()->meta_title_nl);
        $this->assertNull($project->refresh()->meta_description);
        $this->assertNull($project->meta_description_nl);
    }

    public function test_the_project_page_and_dutch_home_serve_the_new_copy(): void
    {
        $this->liveProfile();
        $this->project('data-exchange-portal');
        $this->migration()->up();

        $description = (string) Project::where('slug', 'data-exchange-portal')->value('meta_description_nl');

        $this->get('/nl/work/data-exchange-portal')
            ->assertSee('<meta name="description" content="'.e($description).'">', false);
        $this->get('/nl')
            ->assertSee('<title>'.e((string) Profile::current()->meta_title_nl).'</title>', false);
    }
}
