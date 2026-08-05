<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\PageView;
use App\Models\PageViewTotal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_dashboard_renders_the_inertia_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('projectCount')
                ->has('sparkline')
                ->has('topPaths')
                ->has('activity')
        );
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

        $this->actingAs($user)->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('activity.0.action', 'updated')
                ->where('activity.0.subject', 'Profile')
                ->where('activity.0.label', null)
        );
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

        $this->actingAs($user)->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->where('activity.0.subject', 'Project')
                ->where('activity.0.label', 'Arbo SaaS')
        );
    }

    public function test_page_view_count_includes_rolled_up_totals(): void
    {
        $user = User::factory()->create();
        PageView::create(['path' => '/']);
        PageView::create(['path' => '/work']);
        PageViewTotal::create(['path' => '/', 'views' => 5]);

        $this->actingAs($user)->get('/admin')->assertInertia(
            fn (Assert $page) => $page->where('pageViewCount', 7)
        );
    }

    public function test_top_paths_merge_live_and_rolled_up_counts(): void
    {
        $user = User::factory()->create();

        PageView::create(['path' => '/']);
        PageView::create(['path' => '/docs']);
        PageView::create(['path' => '/docs']);
        PageView::create(['path' => '/docs']);
        PageViewTotal::create(['path' => '/', 'views' => 10]);
        PageViewTotal::create(['path' => '/work', 'views' => 4]);

        $this->actingAs($user)->get('/admin')->assertInertia(
            fn (Assert $page) => $page
                ->where('topPaths.0.path', '/')
                ->where('topPaths.0.views', 11)
                ->where('topPaths.1.path', '/work')
                ->where('topPaths.1.views', 4)
                ->where('topPaths.2.path', '/docs')
                ->where('topPaths.2.views', 3)
        );
    }
}
