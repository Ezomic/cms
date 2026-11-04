<?php

namespace Tests\Feature;

use App\Mail\ContactAcknowledgement;
use App\Mail\ContactFormSubmitted;
use App\Models\Profile;
use App\Support\ContactFormToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactReplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();
    }

    private function agedToken(): string
    {
        $now = now();
        $this->travelTo($now->copy()->subSeconds(10));
        $token = ContactFormToken::issue();
        $this->travelTo($now);

        return $token;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function submit(array $overrides = []): TestResponse
    {
        return $this->post('/contact', [
            'name' => 'Jane Client',
            'email' => 'jane@example.com',
            'message' => 'I would like to discuss a project.',
            ...$overrides,
            'form_token' => $overrides['form_token'] ?? $this->agedToken(),
        ]);
    }

    public function test_a_replayed_token_is_dropped_silently(): void
    {
        $token = $this->agedToken();

        $this->submit(['form_token' => $token])->assertSessionHas('status');
        $this->assertDatabaseCount('contact_submissions', 1);

        $this->submit(['form_token' => $token, 'message' => 'A second message'])
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('contact_submissions', 1);
        Mail::assertSent(ContactFormSubmitted::class, 1);
        Mail::assertQueued(ContactAcknowledgement::class, 1);
    }

    public function test_a_replay_gets_the_same_response_as_the_original(): void
    {
        $token = $this->agedToken();

        $first = $this->submit(['form_token' => $token]);
        $second = $this->submit(['form_token' => $token]);

        $this->assertSame($first->getStatusCode(), $second->getStatusCode());
        $this->assertSame($first->getSession()->get('status'), $second->getSession()->get('status'));
    }

    public function test_a_fresh_token_still_works_after_a_replay(): void
    {
        $token = $this->agedToken();
        $this->submit(['form_token' => $token]);
        $this->submit(['form_token' => $token]);

        $this->submit(['message' => 'Another honest message'])->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 2);
    }

    public function test_two_tokens_issued_in_the_same_second_are_both_usable(): void
    {
        [$a, $b] = $this->travelTo(now()->subSeconds(10), fn () => [ContactFormToken::issue(), ContactFormToken::issue()]);

        $this->assertNotSame($a, $b);

        $this->submit(['form_token' => $a]);
        $this->submit(['form_token' => $b]);

        $this->assertDatabaseCount('contact_submissions', 2);
    }

    public function test_a_validation_failure_does_not_burn_the_token(): void
    {
        $token = $this->agedToken();

        $this->submit(['form_token' => $token, 'email' => 'not-an-email'])->assertSessionHasErrors('email');
        $this->submit(['form_token' => $token])->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 1);
    }

    public function test_the_replay_marker_is_remembered_for_the_tokens_remaining_lifetime(): void
    {
        $token = $this->agedToken();
        $this->submit(['form_token' => $token]);

        $this->travel(100)->minutes();
        $this->submit(['form_token' => $token]);
        $this->assertDatabaseCount('contact_submissions', 1);
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function badTokens(): array
    {
        return [
            'empty' => [''],
            'tampered signature' => ['1700000000.deadbeef.deadbeef'],
            'legacy two part token' => ['1700000000.deadbeef'],
            'no dots' => ['garbage'],
            'only dots' => ['..'],
            'non numeric timestamp' => ['abc.def.ghi'],
            'array' => [['a', 'b']],
            'very long' => [str_repeat('1.', 5000)],
        ];
    }

    #[DataProvider('badTokens')]
    public function test_malformed_or_tampered_tokens_are_dropped_silently(mixed $token): void
    {
        $this->submit(['form_token' => $token])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_a_tampered_timestamp_on_a_real_token_is_dropped(): void
    {
        $token = $this->agedToken();
        [, $nonce, $signature] = explode('.', $token);
        $forged = (now()->getTimestamp() - 20).'.'.$nonce.'.'.$signature;

        $this->submit(['form_token' => $forged]);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_a_just_issued_token_is_dropped_and_an_expired_one_too(): void
    {
        $this->submit(['form_token' => ContactFormToken::issue()]);

        $expired = $this->travelTo(now()->subHours(3), fn () => ContactFormToken::issue());
        $this->submit(['form_token' => $expired]);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_the_acknowledgement_is_limited_to_three_an_hour_per_address(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->submit(['message' => "Message {$i}"])->assertSessionHas('status');
        }

        $this->assertDatabaseCount('contact_submissions', 5);
        Mail::assertSent(ContactFormSubmitted::class, 5);
        Mail::assertQueued(ContactAcknowledgement::class, 3);
    }

    public function test_the_acknowledgement_limit_ignores_case_and_surrounding_whitespace(): void
    {
        foreach (['jane@example.com', 'JANE@example.com', 'Jane@Example.com', 'jAnE@example.com'] as $email) {
            $this->submit(['email' => $email]);
        }

        $this->assertDatabaseCount('contact_submissions', 4);
        Mail::assertQueued(ContactAcknowledgement::class, 3);
    }

    public function test_a_different_address_has_its_own_acknowledgement_allowance(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->submit();
        }

        $this->submit(['email' => 'someone-else@example.com']);

        Mail::assertQueued(ContactAcknowledgement::class, fn ($mail) => $mail->hasTo('someone-else@example.com'));
    }

    public function test_the_acknowledgement_allowance_returns_after_an_hour(): void
    {
        $tokens = array_map(fn () => $this->agedToken(), range(1, 5));

        foreach (array_slice($tokens, 0, 4) as $token) {
            $this->submit(['form_token' => $token]);
        }

        $this->travel(61)->minutes();
        $this->submit(['form_token' => $tokens[4]]);

        Mail::assertQueued(ContactAcknowledgement::class, 4);
    }

    public function test_dutch_contact_route_is_protected_the_same_way(): void
    {
        $token = $this->agedToken();

        $this->post('/nl/contact', ['name' => 'Jan', 'email' => 'jan@example.com', 'message' => 'Hallo daar', 'form_token' => $token]);
        $this->post('/nl/contact', ['name' => 'Jan', 'email' => 'jan@example.com', 'message' => 'Hallo daar', 'form_token' => $token]);

        $this->assertDatabaseCount('contact_submissions', 1);
    }
}
