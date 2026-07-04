<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_url_renders_custom_404_page(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('Page not found.');
        $response->assertSee(route('home'));
        $response->assertSee(route('work.index'));
    }

    public function test_unpublished_project_returns_404(): void
    {
        $project = Project::create(['name' => 'Hidden', 'published' => false, 'sort_order' => 0]);

        $response = $this->get("/work/{$project->slug}");

        $response->assertStatus(404);
        $response->assertSee('Page not found.');
    }
}
