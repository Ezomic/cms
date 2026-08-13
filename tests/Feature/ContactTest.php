<?php

namespace Tests\Feature;

use App\Mail\ContactFormSubmitted;
use App\Models\Profile;
use App\Support\ContactFormToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
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

        $this->submit()
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

        $this->submit()
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_submissions', ['email' => 'jane@example.com']);
    }

    public function test_budget_from_the_form_option_list_is_accepted(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->submit(['budget' => '> €50k'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('contact_submissions', ['budget' => '> €50k']);
        Mail::assertSent(ContactFormSubmitted::class);
    }

    public function test_omitted_budget_is_accepted(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->submit()
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 1);
    }

    public function test_budget_outside_the_option_list_is_dropped_silently(): void
    {
        // Regression for CMS-81: a scraper posts the raw HTML option value back
        // without decoding entities, which the real <select> can never produce.
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->submit(['budget' => '&gt; €50k'])
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_honeypot_skips_persistence_and_mail(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->submit(['website' => 'http://spam.test'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_the_rendered_form_carries_a_token_that_passes_validation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('name="form_token"', false);
    }

    public function test_submission_without_a_form_token_is_dropped_silently(): void
    {
        // CMS-125: a bot posting straight to the endpoint never renders the form.
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->post(route('contact.store'), $this->valid)
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_forged_form_token_is_dropped_silently(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->post(route('contact.store'), [
            ...$this->valid,
            'form_token' => (string) now()->subMinute()->getTimestamp().'.not-a-real-signature',
        ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_submission_faster_than_the_minimum_dwell_time_is_dropped_silently(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $this->post(route('contact.store'), [
            ...$this->valid,
            'form_token' => ContactFormToken::issue(),
        ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_stale_form_token_is_dropped_silently(): void
    {
        // A harvested token must not stay usable indefinitely.
        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();

        $payload = [...$this->valid, 'form_token' => ContactFormToken::issue()];

        $this->travel(3)->hours();

        $this->post(route('contact.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
    }

    private function submit(array $extra = []): TestResponse
    {
        $payload = [...$this->valid, 'form_token' => ContactFormToken::issue(), ...$extra];

        $this->travel(5)->seconds();

        return $this->post(route('contact.store'), $payload);
    }
}
