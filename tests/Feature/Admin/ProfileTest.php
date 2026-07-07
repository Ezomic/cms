<?php

namespace Tests\Feature\Admin;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_admin_routes(): void
    {
        $this->get('/admin/profile')->assertRedirect('/admin/login');
    }

    public function test_admin_can_update_the_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => 'Jane Developer',
            'city' => 'Amsterdam',
            'tagline' => 'Backend Engineer',
            'hero_headline' => 'Building solid backends.',
            'available' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('profile', ['name' => 'Jane Developer', 'city' => 'Amsterdam', 'available' => true]);
    }

    public function test_updating_the_profile_requires_core_fields(): void
    {
        $user = User::factory()->create();
        Profile::current();

        $response = $this->actingAs($user)->put('/admin/profile', ['name' => '']);

        $response->assertSessionHasErrors(['name', 'city', 'tagline', 'hero_headline']);
    }
}
