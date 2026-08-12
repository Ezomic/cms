<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RelatedProjectsTest extends TestCase
{
    use RefreshDatabase;

    private function project(string $name, string $tags, bool $published = true, int $order = 0): Project
    {
        return Project::create([
            'name' => $name,
            'tags' => $tags,
            'body' => '<p>Body.</p>',
            'published' => $published,
            'sort_order' => $order,
        ]);
    }

    public function test_it_ranks_by_number_of_shared_tags(): void
    {
        $subject = $this->project('Subject', 'laravel,vue,mysql', true, 0);
        $this->project('Two Shared', 'laravel,vue', true, 5);
        $this->project('One Shared', 'laravel', true, 1);

        $related = $subject->relatedProjects();

        $this->assertSame('Two Shared', $related->first()->name);
    }

    public function test_it_excludes_the_current_project(): void
    {
        $subject = $this->project('Subject', 'laravel', true, 0);
        $this->project('Other', 'laravel', true, 1);

        $related = $subject->relatedProjects();

        $this->assertFalse($related->contains(fn (Project $p) => $p->id === $subject->id));
    }

    public function test_it_never_includes_unpublished_projects(): void
    {
        $subject = $this->project('Subject', 'laravel', true, 0);
        $this->project('Draft', 'laravel', false, 1);
        $this->project('Live', 'laravel', true, 2);

        $related = $subject->relatedProjects();

        $this->assertTrue($related->contains(fn (Project $p) => $p->name === 'Live'));
        $this->assertFalse($related->contains(fn (Project $p) => $p->name === 'Draft'));
    }

    public function test_it_falls_back_to_sort_order_when_no_tags_are_shared(): void
    {
        $subject = $this->project('Subject', 'cobol', true, 0);
        $this->project('First', 'laravel', true, 1);
        $this->project('Second', 'vue', true, 2);

        $related = $subject->relatedProjects();

        $this->assertCount(2, $related);
        $this->assertSame('First', $related->first()->name);
    }

    public function test_it_tops_up_partial_matches_with_the_fallback(): void
    {
        $subject = $this->project('Subject', 'laravel', true, 0);
        $this->project('Shared', 'laravel', true, 9);
        $this->project('Unrelated A', 'cobol', true, 1);
        $this->project('Unrelated B', 'fortran', true, 2);

        $related = $subject->relatedProjects();

        $this->assertCount(3, $related);
        $this->assertSame('Shared', $related->first()->name);
    }

    public function test_it_caps_at_the_limit(): void
    {
        $subject = $this->project('Subject', 'laravel', true, 0);

        foreach (range(1, 6) as $i) {
            $this->project("Other {$i}", 'laravel', true, $i);
        }

        $this->assertCount(3, $subject->relatedProjects());
    }

    public function test_the_section_renders_on_the_case_study_page(): void
    {
        $this->project('Subject', 'laravel', true, 0);
        $this->project('Neighbour', 'laravel', true, 1);

        $response = $this->get('/work/subject');

        $response->assertOk();
        $response->assertSee('More work');
        $response->assertSee('Neighbour');
        $response->assertSee(route('project.show', 'neighbour'), false);
    }

    public function test_the_section_is_hidden_when_there_is_nothing_else_published(): void
    {
        $this->project('Lonely', 'laravel', true, 0);

        $response = $this->get('/work/lonely');

        $response->assertOk();
        $response->assertDontSee('More work');
    }

    public function test_a_preview_page_shows_related_work_without_leaking_drafts(): void
    {
        $draft = $this->project('Draft Subject', 'laravel', false, 0);
        $this->project('Live Neighbour', 'laravel', true, 1);
        $this->project('Other Draft', 'laravel', false, 2);

        $url = URL::temporarySignedRoute('project.preview', now()->addDay(), ['project' => $draft->slug]);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Live Neighbour');
        $response->assertDontSee('Other Draft');
    }

    public function test_related_links_use_the_dutch_prefix_on_the_dutch_page(): void
    {
        $this->project('Subject', 'laravel', true, 0);
        $this->project('Neighbour', 'laravel', true, 1);

        $response = $this->get('/nl/work/subject');

        $response->assertOk();
        $response->assertSee('/nl/work/neighbour', false);
    }
}
