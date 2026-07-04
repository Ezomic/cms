<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OgImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_og_image_renders_a_valid_png(): void
    {
        $response = $this->get('/og/home.png');

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function test_project_og_image_renders_a_valid_png(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'client_name' => 'Acme BV',
            'year' => '2026',
            'tags' => 'Laravel, Vue',
            'body' => 'Some case study body.',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get("/og/work/{$project->slug}.png");

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }
}
