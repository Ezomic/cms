<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_lists_published_posts(): void
    {
        Post::create(['title' => 'Published Post', 'published' => true]);
        Post::create(['title' => 'Draft Post', 'published' => false]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    public function test_published_post_is_viewable(): void
    {
        $post = Post::create([
            'title' => 'My Great Post',
            'body' => '<p>Hello world.</p>',
            'published' => true,
        ]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(200);
        $response->assertSee('My Great Post');
        $response->assertSee('Hello world.', false);
    }

    public function test_unpublished_post_returns_404(): void
    {
        $post = Post::create(['title' => 'Not Ready Yet', 'published' => false]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(404);
    }

    public function test_post_slug_is_generated_from_title(): void
    {
        $post = Post::create(['title' => 'Loop Prompts Are Great', 'published' => true]);

        $this->assertSame('loop-prompts-are-great', $post->slug);
    }
}
