<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_security(): void
    {
        $response = $this->get('/admin/security');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_view_security_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/security');

        $response->assertOk();
        $response->assertViewIs('admin.security.show');
        $response->assertViewHas('passkeys');
    }
}
