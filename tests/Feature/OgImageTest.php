<?php

namespace Tests\Feature;

use App\Models\Post;
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

    public function test_post_og_image_renders_a_valid_png(): void
    {
        $post = Post::create([
            'title' => 'Test Post',
            'excerpt' => 'A short summary of the post.',
            'body' => 'Some blog post body.',
            'published' => true,
        ]);

        $response = $this->get("/og/blog/{$post->slug}.png");

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function test_post_og_image_404s_for_unpublished_post(): void
    {
        $post = Post::create([
            'title' => 'Draft Post',
            'body' => 'Draft body.',
            'published' => false,
        ]);

        $this->get("/og/blog/{$post->slug}.png")->assertNotFound();
    }
}
