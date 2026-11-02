<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagMergeMigrationTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function migrate(): void
    {
        $migration = require database_path('migrations/2026_11_02_100000_merge_duplicate_project_tags.php');
        $this->assertInstanceOf(Migration::class, $migration);
        $migration->up();
    }

    private function tagged(string $tags, bool $published = true): Project
    {
        $this->sequence++;

        return Project::create(['name' => 'P'.$this->sequence, 'tags' => $tags, 'published' => $published, 'sort_order' => $this->sequence]);
    }

    public function test_it_merges_the_three_known_duplicate_pairs_to_one_canonical_form(): void
    {
        $a = $this->tagged('Laravel, Vue 3, Inertia, Tailwind');
        $b = $this->tagged('Laravel, Vue.js, Inertia.js, Tailwind CSS');

        $this->migrate();

        $this->assertSame('Laravel, Vue.js, Inertia.js, Tailwind CSS', $a->fresh()->tags);
        $this->assertSame('Laravel, Vue.js, Inertia.js, Tailwind CSS', $b->fresh()->tags);
    }

    public function test_it_catches_case_and_spacing_variants(): void
    {
        $project = $this->tagged('vue,  VUE 3 ,tailwindcss, inertia.JS');

        $this->migrate();

        $this->assertSame('Vue.js, Tailwind CSS, Inertia.js', $project->fresh()->tags);
    }

    public function test_it_removes_duplicates_the_merge_creates_within_one_project(): void
    {
        $project = $this->tagged('Vue, Vue 3, Vue.js, PHP');

        $this->migrate();

        $this->assertSame('Vue.js, PHP', $project->fresh()->tags);
    }

    public function test_it_leaves_unrelated_tags_and_untagged_projects_alone(): void
    {
        $plain = $this->tagged('Laravel, PHP, .NET 9');
        $none = Project::create(['name' => 'None', 'published' => true, 'sort_order' => 99]);

        $this->migrate();

        $this->assertSame('Laravel, PHP, .NET 9', $plain->fresh()->tags);
        $this->assertNull($none->fresh()->tags);
    }

    public function test_it_rewrites_unpublished_and_trashed_projects_too(): void
    {
        $draft = $this->tagged('Vue 3', published: false);
        $trashed = $this->tagged('Tailwind');
        $trashed->delete();

        $this->migrate();

        $this->assertSame('Vue.js', $draft->fresh()->tags);
        $this->assertSame('Tailwind CSS', Project::withTrashed()->findOrFail($trashed->id)->tags);
    }

    public function test_it_folds_other_case_variants_to_the_most_common_spelling(): void
    {
        $this->tagged('Laravel');
        $this->tagged('Laravel');
        $odd = $this->tagged('laravel, PHP');

        $this->migrate();

        $this->assertSame('Laravel, PHP', $odd->fresh()->tags);
    }

    public function test_it_is_idempotent(): void
    {
        $project = $this->tagged('Vue 3, Inertia');

        $this->migrate();
        $this->migrate();

        $this->assertSame('Vue.js, Inertia.js', $project->fresh()->tags);
    }

    public function test_the_merged_tag_has_a_single_page_listing_every_project(): void
    {
        $this->tagged('Vue 3');
        $this->tagged('Vue.js');
        $this->tagged('Vue');

        $this->migrate();

        $this->get(route('work.tag', 'Vue.js'))->assertOk();
        $this->assertSame(3, Project::where('tags', 'Vue.js')->count());
    }
}
