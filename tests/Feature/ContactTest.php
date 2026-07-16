<?php

namespace Tests\Feature;

use App\Mail\ContactFormSubmitted;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private array $valid = [
        'name' => 'Jane Client',
        'email' => 'jane@example.com',
        'message' => 'I would like to discuss a project.',
    ];

    public function test_submission_is_saved_and_notification_is_sent_synchronously(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->post(route('contact.store'), $this->valid)
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_submissions', ['email' => 'jane@example.com']);

        Mail::assertSent(ContactFormSubmitted::class);
        Mail::assertNotQueued(ContactFormSubmitted::class);
    }

    public function test_mail_failure_does_not_break_the_submission(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP unavailable'));

        $this->post(route('contact.store'), $this->valid)
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_submissions', ['email' => 'jane@example.com']);
    }

    public function test_honeypot_skips_persistence_and_mail(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->post(route('contact.store'), [...$this->valid, 'website' => 'http://spam.test'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }
}
