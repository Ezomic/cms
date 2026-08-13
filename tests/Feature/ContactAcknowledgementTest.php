<?php

namespace Tests\Feature;

use App\Mail\ContactAcknowledgement;
use App\Mail\ContactFormSubmitted;
use App\Models\Profile;
use App\Support\ContactFormToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactAcknowledgementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'I would like to build a thing.',
            'budget' => '',
            // CMS-125 requires a minimum dwell time, so a token issued this
            // instant is rejected as scripted. Issue it in the past to model a
            // visitor who actually read the form.
            'form_token' => $this->travelTo(now()->subSeconds(10), fn () => ContactFormToken::issue()),
        ], $overrides);
    }

    public function test_a_valid_submission_acknowledges_the_sender(): void
    {
        $this->post('/contact', $this->payload());

        Mail::assertQueued(ContactAcknowledgement::class, fn ($mail) => $mail->hasTo('jane@example.com'));
    }

    public function test_exactly_one_acknowledgement_is_sent(): void
    {
        $this->post('/contact', $this->payload());

        Mail::assertQueuedCount(1);
    }

    /**
     * The acknowledgement goes to an address the submitter chose, so anything
     * the bot checks reject must stay completely silent. Otherwise the form
     * becomes a way to mail strangers.
     */
    public function test_a_honeypot_hit_sends_nothing(): void
    {
        $response = $this->post('/contact', $this->payload(['website' => 'http://spam.example']));

        Mail::assertNothingQueued();
        $response->assertSessionHas('status');
    }

    public function test_a_forged_budget_sends_nothing(): void
    {
        $response = $this->post('/contact', $this->payload(['budget' => '&gt; €50k']));

        Mail::assertNothingQueued();
        $response->assertSessionHas('status');
    }

    public function test_a_missing_form_token_sends_nothing(): void
    {
        $response = $this->post('/contact', $this->payload(['form_token' => '']));

        Mail::assertNothingQueued();
        $response->assertSessionHas('status');
    }

    public function test_a_rejected_submission_is_not_saved_either(): void
    {
        $this->post('/contact', $this->payload(['website' => 'spam']));

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_the_acknowledgement_never_quotes_the_submitted_message(): void
    {
        $secret = 'PAYLOAD-THAT-MUST-NOT-BE-RELAYED';

        $this->post('/contact', $this->payload(['message' => $secret]));

        Mail::assertQueued(ContactAcknowledgement::class, function (ContactAcknowledgement $mail) use ($secret) {
            $rendered = $mail->render();

            $this->assertStringNotContainsString($secret, $rendered);

            return true;
        });
    }

    public function test_it_is_queued_rather_than_sent_inline(): void
    {
        $this->post('/contact', $this->payload());

        Mail::assertQueued(ContactAcknowledgement::class);
        Mail::assertNotSent(ContactAcknowledgement::class);
    }

    public function test_replies_are_directed_at_the_site_owner(): void
    {
        $this->post('/contact', $this->payload());

        Mail::assertQueued(ContactAcknowledgement::class, function (ContactAcknowledgement $mail) {
            $envelope = $mail->envelope();

            return collect($envelope->replyTo)->contains(fn ($address) => $address->address === 'owner@example.com');
        });
    }

    /**
     * Posted to the real Dutch endpoint rather than forcing the locale by
     * hand. /contact used to be registered once, outside the locale group, so
     * a Dutch visitor's submission ran in English and got an English reply.
     */
    public function test_a_dutch_submission_is_acknowledged_in_dutch(): void
    {
        $this->post('/nl/contact', $this->payload(['email' => 'dutch@example.com']))
            ->assertRedirect();

        Mail::assertQueued(ContactAcknowledgement::class, function (ContactAcknowledgement $mail) {
            $this->assertTrue($mail->hasTo('dutch@example.com'));
            $this->assertStringContainsString('werkdag', $mail->render());
            $this->assertStringNotContainsString('business day', $mail->render());

            return true;
        });
    }

    public function test_an_english_submission_is_acknowledged_in_english(): void
    {
        $this->post('/contact', $this->payload());

        Mail::assertQueued(ContactAcknowledgement::class, function (ContactAcknowledgement $mail) {
            $this->assertStringContainsString('business day', $mail->render());
            $this->assertStringNotContainsString('werkdag', $mail->render());

            return true;
        });
    }

    public function test_the_dutch_form_posts_to_the_dutch_endpoint(): void
    {
        preg_match('/<form method="POST" action="([^"]+)"/', (string) $this->get('/nl')->getContent(), $matches);

        $this->assertSame(route('nl.contact.store'), $matches[1] ?? null);
    }

    public function test_the_english_form_posts_to_the_unprefixed_endpoint(): void
    {
        preg_match('/<form method="POST" action="([^"]+)"/', (string) $this->get('/')->getContent(), $matches);

        $this->assertSame(route('contact.store'), $matches[1] ?? null);
    }

    public function test_the_owner_notification_still_sends_synchronously(): void
    {
        $this->post('/contact', $this->payload());

        Mail::assertSent(ContactFormSubmitted::class, fn ($mail) => $mail->hasTo('owner@example.com'));
    }

    public function test_a_failure_to_queue_does_not_break_the_visitor_response(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $response = $this->post('/contact', $this->payload());

        $response->assertSessionHas('status');
        $this->assertDatabaseCount('contact_submissions', 1);
    }
}
