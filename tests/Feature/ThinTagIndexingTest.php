<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThinTagIndexingTest extends TestCase
{
    use RefreshDatabase;

    private function projectsTagged(string $tag, int $count, bool $published = true): void
    {
        foreach (range(1, $count) as $n) {
            Project::create([
                'name' => "{$tag} project {$n}",
                'tags' => $tag,
                'published' => $published,
                'sort_order' => $n,
            ]);
        }
    }

    public function test_the_threshold_is_three_projects(): void
    {
        $this->assertSame(3, Project::MIN_PROJECTS_FOR_INDEXED_TAG);
    }

    public function test_a_tag_on_two_projects_is_noindex_follow(): void
    {
        $this->projectsTagged('Svelte', 2);

        $this->get(route('work.tag', 'Svelte'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_a_tag_on_three_projects_is_indexable(): void
    {
        $this->projectsTagged('Laravel', 3);

        $this->get(route('work.tag', 'Laravel'))
            ->assertOk()
            ->assertDontSee('name="robots"', false);
    }

    public function test_the_dutch_tag_page_follows_the_same_rule(): void
    {
        $this->projectsTagged('Svelte', 2);

        $this->get(route('nl.work.tag', 'Svelte'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_unpublished_projects_do_not_count_towards_the_threshold(): void
    {
        $this->projectsTagged('Laravel', 2);
        Project::create(['name' => 'Draft', 'tags' => 'Laravel', 'published' => false, 'sort_order' => 9]);

        $this->get(route('work.tag', 'Laravel'))
            ->assertOk()
            ->assertSee('noindex, follow', false);
    }

    public function test_a_thin_tag_page_still_filters_its_projects(): void
    {
        $this->projectsTagged('Svelte', 2);
        Project::create(['name' => 'Unrelated build', 'tags' => 'Laravel', 'published' => true, 'sort_order' => 9]);

        $this->get(route('work.tag', 'Svelte'))
            ->assertOk()
            ->assertSee('Svelte project 1')
            ->assertSee('Svelte project 2')
            ->assertDontSee('Unrelated build');
    }

    public function test_the_sitemap_omits_thin_tags_and_keeps_the_rest(): void
    {
        $this->projectsTagged('Laravel', 3);
        $this->projectsTagged('Svelte', 2);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>'.route('work.tag', 'Laravel').'</loc>', false)
            ->assertSee('<loc>'.route('nl.work.tag', 'Laravel').'</loc>', false)
            ->assertDontSee(route('work.tag', 'Svelte'), false)
            ->assertDontSee(route('nl.work.tag', 'Svelte'), false);
    }

    public function test_a_tag_repeated_on_one_project_counts_once(): void
    {
        $this->projectsTagged('Laravel', 1);
        Project::create(['name' => 'Repeater', 'tags' => 'Laravel, Laravel', 'published' => true, 'sort_order' => 9]);

        $this->get('/sitemap.xml')->assertDontSee(route('work.tag', 'Laravel'), false);
    }
}
