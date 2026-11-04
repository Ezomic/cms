<?php

namespace Tests\Feature\Auth;

use App\Mail\LoginCodeMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LoginCodeHardeningTest extends TestCase
{
    use RefreshDatabase;

    private int $ipCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function fromFreshIp(): static
    {
        $this->ipCounter++;

        return $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.'.$this->ipCounter]);
    }

    private function verify(string $email, string $code): TestResponse
    {
        return $this->fromFreshIp()->post('/admin/login/code/verify', ['email' => $email, 'code' => $code]);
    }

    private function send(string $email): TestResponse
    {
        return $this->fromFreshIp()->post('/admin/login/code', ['email' => $email]);
    }

    private function issuedCode(): string
    {
        $codes = [];

        Mail::assertQueued(LoginCodeMail::class, function (LoginCodeMail $mail) use (&$codes) {
            $codes[] = $mail->code;

            return true;
        });

        return (string) end($codes);
    }

    public function test_the_sixth_wrong_code_for_one_email_is_blocked_even_from_another_ip(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->verify($user->email, '000000')->assertSessionHasErrors('code');
        }

        $this->verify($user->email, '000000')->assertStatus(429);
    }

    public function test_a_different_email_is_unaffected_by_another_emails_failures(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->verify($user->email, '000000');
        }

        $this->verify($other->email, '000000')->assertSessionHasErrors('code');
    }

    public function test_case_variants_of_an_email_share_one_bucket(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        foreach (['admin@example.com', 'ADMIN@example.com', 'Admin@Example.com', 'aDmIn@example.com', 'admin@EXAMPLE.com'] as $variant) {
            $this->verify($variant, '000000');
        }

        $this->verify($user->email, '000000')->assertStatus(429);
    }

    public function test_surrounding_whitespace_does_not_open_a_new_bucket(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->verify($i % 2 === 0 ? ' admin@example.com ' : "admin@example.com\t", '000000');
        }

        $this->verify($user->email, '000000')->assertStatus(429);
    }

    public function test_plus_addressing_is_a_distinct_address_with_its_own_bucket(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        for ($i = 0; $i < 6; $i++) {
            $this->verify($user->email, '000000');
        }

        $this->verify('admin+x@example.com', '000000')->assertSessionHasErrors('code');
    }

    public function test_an_email_that_does_not_exist_is_limited_the_same_way(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->verify('nobody@example.com', '000000')->assertSessionHasErrors('code');
        }

        $this->verify('nobody@example.com', '000000')->assertStatus(429);
    }

    public function test_sending_codes_is_limited_per_email_across_ips(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->send($user->email)->assertRedirect(route('admin.login.code.challenge'));
        }

        $this->send($user->email)->assertStatus(429);
        $this->send('someone-else@example.com')->assertRedirect(route('admin.login.code.challenge'));
    }

    public function test_send_and_verify_buckets_do_not_starve_each_other(): void
    {
        $user = User::factory()->create();
        $this->send($user->email);

        for ($i = 0; $i < 5; $i++) {
            $this->verify($user->email, '000000')->assertSessionHasErrors('code');
        }
    }

    public function test_odd_email_values_never_cause_a_server_error(): void
    {
        $odd = [
            'a very long address' => str_repeat('a', 5000).'@example.com',
            'unicode' => 'ünïcödé-用户@例え.jp',
            'array' => ['a@example.com', 'b@example.com'],
            'empty' => '',
            'only spaces' => '   ',
            'null byte' => "admin@example.com\0",
        ];

        foreach ($odd as $label => $value) {
            $this->fromFreshIp()->post('/admin/login/code', ['email' => $value])->assertStatus(302);
            $this->fromFreshIp()->post('/admin/login/code/verify', ['email' => $value, 'code' => '000000'])->assertStatus(302);
            $this->assertGuest();
        }
    }

    public function test_odd_code_values_never_cause_a_server_error(): void
    {
        $user = User::factory()->create();

        foreach ([['x'], str_repeat('9', 10000), '', '０１２３４５', "12\n34"] as $code) {
            $this->fromFreshIp()->post('/admin/login/code/verify', ['email' => $user->email, 'code' => $code])->assertStatus(302);
            $this->assertGuest();
        }
    }

    public function test_the_code_is_cleared_after_five_failures_and_the_correct_code_then_fails(): void
    {
        $user = User::factory()->create();
        $this->send($user->email);
        $code = $this->issuedCode();

        for ($i = 0; $i < 5; $i++) {
            $this->verify($user->email, '000000')->assertSessionHasErrors('code');
        }

        $user->refresh();
        $this->assertNull($user->login_code_hash);
        $this->assertNull($user->login_code_expires_at);

        $this->travel(2)->minutes();

        $this->verify($user->email, $code)->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_four_failures_do_not_clear_the_code(): void
    {
        $user = User::factory()->create();
        $this->send($user->email);
        $code = $this->issuedCode();

        for ($i = 0; $i < 4; $i++) {
            $this->verify($user->email, '000000');
        }

        $this->verify($user->email, $code)->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_failures_are_counted_per_user_not_per_ip(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->send($user->email);
        $this->send($other->email);

        for ($i = 0; $i < 5; $i++) {
            $this->verify($user->email, '000000');
        }

        $this->assertNull($user->fresh()->login_code_hash);
        $this->assertNotNull($other->fresh()->login_code_hash);
    }

    public function test_a_fresh_code_resets_the_failure_count(): void
    {
        $user = User::factory()->create();
        $this->send($user->email);

        for ($i = 0; $i < 4; $i++) {
            $this->verify($user->email, '000000');
        }

        $this->travel(2)->minutes();
        $user->forceFill(['login_code_expires_at' => now()->subMinute()])->save();
        $this->send($user->email);
        $code = $this->issuedCode();

        $this->verify($user->email, '000000');

        $this->verify($user->email, $code)->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_resend_within_sixty_seconds_keeps_the_first_code_valid(): void
    {
        $user = User::factory()->create();

        $first = $this->send($user->email);
        $firstCode = $this->issuedCode();
        $this->travel(30)->seconds();
        $second = $this->send($user->email);

        Mail::assertQueuedCount(1);
        $this->assertSame($first->getTargetUrl(), $second->getTargetUrl());
        $this->assertSame($first->getSession()->get('status'), $second->getSession()->get('status'));

        $this->verify($user->email, $firstCode)->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_resend_after_sixty_seconds_issues_a_new_code(): void
    {
        $user = User::factory()->create();

        $this->send($user->email);
        $firstCode = $this->issuedCode();
        $this->travel(61)->seconds();
        $this->send($user->email);

        Mail::assertQueuedCount(2);
        $secondCode = $this->issuedCode();

        if ($firstCode !== $secondCode) {
            $this->verify($user->email, $firstCode)->assertSessionHasErrors('code');
        }

        $this->verify($user->email, $secondCode)->assertRedirect(route('admin.dashboard'));
    }

    public function test_clearing_a_code_after_failures_does_not_allow_an_immediate_fresh_one(): void
    {
        $user = User::factory()->create();
        $this->send($user->email);

        for ($i = 0; $i < 5; $i++) {
            $this->verify($user->email, '000000');
        }

        $this->travel(2)->seconds();
        $this->send($user->email);

        Mail::assertQueuedCount(1);
        $this->assertNull($user->fresh()->login_code_hash);

        $this->travel(61)->seconds();
        $this->send($user->email);

        Mail::assertQueuedCount(2);
    }

    public function test_the_queued_login_code_payload_is_encrypted(): void
    {
        $this->assertInstanceOf(ShouldBeEncrypted::class, new LoginCodeMail('123456'));
    }

    public function test_the_login_code_mail_is_queued_not_sent_inline(): void
    {
        $user = User::factory()->create();

        $this->send($user->email);

        Mail::assertQueued(LoginCodeMail::class, fn ($mail) => $mail->hasTo($user->email));
        Mail::assertNotSent(LoginCodeMail::class);
    }

    public function test_known_and_unknown_emails_get_identical_responses(): void
    {
        $user = User::factory()->create();

        $known = $this->send($user->email);
        $unknown = $this->send('nobody@example.com');

        $this->assertSame($known->getStatusCode(), $unknown->getStatusCode());
        $this->assertSame($known->getTargetUrl(), $unknown->getTargetUrl());
        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
    }

    public function test_guests_still_cannot_reach_admin_and_a_valid_login_still_works(): void
    {
        $user = User::factory()->create();

        $this->get('/admin')->assertRedirect('/admin/login');

        $this->send($user->email);
        $this->verify($user->email, $this->issuedCode())->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk();
    }
}
