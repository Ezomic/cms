<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\Project;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function draft(): Project
    {
        return Project::create([
            'name' => 'Draft Project',
            'body' => '<p>Work in progress.</p>',
            'published' => false,
            'sort_order' => 0,
        ]);
    }

    private function previewUrl(Project $project, ?DateTimeInterface $expiresAt = null): string
    {
        return URL::temporarySignedRoute(
            'project.preview',
            $expiresAt ?? now()->addDay(),
            ['project' => $project->slug]
        );
    }

    public function test_a_signed_link_renders_an_unpublished_project(): void
    {
        $project = $this->draft();

        $response = $this->get($this->previewUrl($project));

        $response->assertOk();
        $response->assertSee('Draft Project');
        $response->assertSee('Work in progress.', false);
    }

    public function test_the_preview_page_is_not_indexable(): void
    {
        $response = $this->get($this->previewUrl($this->draft()));

        $response->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_the_public_page_stays_indexable(): void
    {
        $project = Project::create(['name' => 'Live Project', 'published' => true, 'sort_order' => 0]);

        $response = $this->get('/work/live-project');

        $response->assertOk();
        $response->assertDontSee('noindex', false);
    }

    public function test_an_unsigned_preview_url_is_rejected(): void
    {
        $project = $this->draft();

        $this->get("/work/{$project->slug}/preview")->assertForbidden();
    }

    public function test_a_tampered_signature_is_rejected(): void
    {
        $project = $this->draft();
        $url = $this->previewUrl($project);

        $this->get($url.'x')->assertForbidden();
    }

    public function test_an_expired_link_is_rejected(): void
    {
        $project = $this->draft();
        $url = $this->previewUrl($project, now()->subMinute());

        $this->get($url)->assertForbidden();
    }

    public function test_the_unpublished_project_still_404s_on_its_public_url(): void
    {
        $project = $this->draft();

        $this->get("/work/{$project->slug}")->assertNotFound();
    }

    public function test_preview_hits_are_not_recorded_as_page_views(): void
    {
        $project = $this->draft();

        $this->get($this->previewUrl($project))->assertOk();

        $this->assertSame(0, PageView::count());
    }

    public function test_preview_urls_are_absent_from_the_sitemap(): void
    {
        Project::create(['name' => 'Live Project', 'published' => true, 'sort_order' => 0]);
        $this->draft();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee('preview');
        $response->assertDontSee('draft-project');
    }

    public function test_the_edit_screen_exposes_a_signed_preview_url(): void
    {
        $project = $this->draft();

        $this->actingAs(User::factory()->create())
            ->get("/admin/projects/{$project->id}/edit")
            ->assertInertia(fn (Assert $page) => $page
                ->where('project.preview_url', fn (string $url): bool => str_contains($url, "/work/{$project->slug}/preview")
                    && str_contains($url, 'signature=')
                    && str_contains($url, 'expires=')
                )
                ->etc()
            );
    }
}
