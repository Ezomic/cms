<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScreensRenderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every admin screen renders its expected Inertia page component.
     */
    public function test_all_admin_screens_render_their_inertia_component(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'P', 'body' => 'x', 'published' => true, 'sort_order' => 0]);
        $testimonial = Testimonial::create(['quote' => 'Great', 'author_name' => 'A']);
        $skill = Skill::create(['name' => 'PHP', 'category' => 'Backend', 'sort_order' => 0]);
        $post = Post::create(['title' => 'Post', 'published' => true]);

        $screens = [
            '/admin' => 'Dashboard',
            '/admin/projects' => 'Projects/Index',
            '/admin/projects/create' => 'Projects/Form',
            "/admin/projects/{$project->id}/edit" => 'Projects/Form',
            '/admin/projects/trash' => 'Projects/Trash',
            '/admin/testimonials' => 'Testimonials/Index',
            '/admin/testimonials/create' => 'Testimonials/Form',
            "/admin/testimonials/{$testimonial->id}/edit" => 'Testimonials/Form',
            '/admin/testimonials/trash' => 'Testimonials/Trash',
            '/admin/skills' => 'Skills/Index',
            '/admin/skills/create' => 'Skills/Form',
            "/admin/skills/{$skill->id}/edit" => 'Skills/Form',
            '/admin/skills/trash' => 'Skills/Trash',
            '/admin/posts' => 'Posts/Index',
            '/admin/posts/create' => 'Posts/Form',
            "/admin/posts/{$post->id}/edit" => 'Posts/Form',
            '/admin/posts/trash' => 'Posts/Trash',
            '/admin/contact-submissions' => 'ContactSubmissions/Index',
            '/admin/profile' => 'Profile/Edit',
            '/admin/users' => 'Users/Index',
            '/admin/users/create' => 'Users/Form',
            '/admin/security' => 'Security/Show',
        ];

        foreach ($screens as $url => $component) {
            $this->actingAs($user)->get($url)->assertInertia(
                fn (Assert $page) => $page->component($component)
            );
        }
    }
}
