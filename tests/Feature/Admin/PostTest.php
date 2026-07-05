<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_post_admin_routes(): void
    {
        $post = Post::create(['title' => 'Test Post']);

        $this->get('/admin/posts')->assertRedirect('/admin/login');
        $this->get("/admin/posts/{$post->id}/edit")->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', [
            'title' => 'New Post',
            'excerpt' => 'A short summary.',
            'body' => '<p>Body content.</p>',
            'published' => '1',
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', ['title' => 'New Post', 'published' => true]);
    }

    public function test_creating_a_post_requires_a_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', ['title' => '']);

        $response->assertSessionHasErrors('title');
    }

    public function test_admin_can_update_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['title' => 'Old Title']);

        $response = $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_delete_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['title' => 'To Delete']);

        $response = $this->actingAs($user)->delete("/admin/posts/{$post->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_admin_can_restore_a_deleted_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['title' => 'To Restore']);
        $post->delete();

        $response = $this->actingAs($user)->post("/admin/posts/{$post->id}/restore");

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'deleted_at' => null]);
    }
}
