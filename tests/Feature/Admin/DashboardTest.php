<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
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
}
