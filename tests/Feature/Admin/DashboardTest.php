<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\PageView;
use App\Models\PageViewTotal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_activity_feed_hides_redundant_label_matching_subject_type(): void
    {
        $user = User::factory()->create();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'updated',
            'subject_type' => 'Profile',
            'subject_label' => 'Profile',
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('updated');
        $response->assertSee('Profile');
        $response->assertDontSee('"Profile"', false);
    }

    public function test_activity_feed_shows_label_when_it_differs_from_subject_type(): void
    {
        $user = User::factory()->create();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'updated',
            'subject_type' => 'Project',
            'subject_label' => 'Arbo SaaS',
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('"Arbo SaaS"', false);
    }

    public function test_page_view_count_includes_rolled_up_totals(): void
    {
        $user = User::factory()->create();
        PageView::create(['path' => '/']);
        PageView::create(['path' => '/work']);
        PageViewTotal::create(['path' => '/', 'views' => 5]);

        $this->actingAs($user)->get('/admin')->assertViewHas('pageViewCount', 7);
    }

    public function test_top_paths_merge_live_and_rolled_up_counts(): void
    {
        $user = User::factory()->create();

        PageView::create(['path' => '/']);
        PageView::create(['path' => '/blog']);
        PageView::create(['path' => '/blog']);
        PageView::create(['path' => '/blog']);
        PageViewTotal::create(['path' => '/', 'views' => 10]);
        PageViewTotal::create(['path' => '/work', 'views' => 4]);

        $this->actingAs($user)->get('/admin')->assertViewHas('topPaths', function ($paths) {
            return $paths[0]->path === '/' && $paths[0]->views === 11
                && $paths[1]->path === '/work' && $paths[1]->views === 4
                && $paths[2]->path === '/blog' && $paths[2]->views === 3;
        });
    }
}
