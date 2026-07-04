<?php

namespace Tests\Feature\Admin;

use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_contact_submission_admin_routes(): void
    {
        $this->get('/admin/contact-submissions')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_contact_submissions(): void
    {
        $user = User::factory()->create();
        ContactSubmission::create(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => 'Hello there.']);

        $response = $this->actingAs($user)->get('/admin/contact-submissions');

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee('Hello there.');
    }

    public function test_admin_can_mark_a_submission_as_read(): void
    {
        $user = User::factory()->create();
        $submission = ContactSubmission::create(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => 'Hi']);

        $this->assertNull($submission->read_at);

        $response = $this->actingAs($user)->post("/admin/contact-submissions/{$submission->id}/read");

        $response->assertRedirect();
        $this->assertNotNull($submission->fresh()->read_at);
    }

    public function test_admin_can_mark_a_submission_as_unread(): void
    {
        $user = User::factory()->create();
        $submission = ContactSubmission::create(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => 'Hi']);
        $submission->forceFill(['read_at' => now()])->save();

        $response = $this->actingAs($user)->post("/admin/contact-submissions/{$submission->id}/unread");

        $response->assertRedirect();
        $this->assertNull($submission->fresh()->read_at);
    }

    public function test_admin_can_delete_a_submission(): void
    {
        $user = User::factory()->create();
        $submission = ContactSubmission::create(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => 'Hi']);

        $response = $this->actingAs($user)->delete("/admin/contact-submissions/{$submission->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('contact_submissions', ['id' => $submission->id]);
    }
}
