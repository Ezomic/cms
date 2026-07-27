<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The blog is hidden pre-launch (CMS-88): the public routes are not
     * registered, so both the index and post URLs 404 for now.
     */
    public function test_blog_index_is_hidden(): void
    {
        Post::create(['title' => 'Published Post', 'published' => true]);

        $this->get('/blog')->assertNotFound();
    }

    public function test_blog_post_is_hidden(): void
    {
        $post = Post::create(['title' => 'My Great Post', 'body' => '<p>Hi.</p>', 'published' => true]);

        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_post_slug_is_generated_from_title(): void
    {
        $post = Post::create(['title' => 'Loop Prompts Are Great', 'published' => true]);

        $this->assertSame('loop-prompts-are-great', $post->slug);
    }
}
