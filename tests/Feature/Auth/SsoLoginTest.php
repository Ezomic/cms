<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;
use Thijssensoftware\IdClient\Exceptions\AccessDeniedException;

class SsoLoginTest extends TestCase
{
    use RefreshDatabase;

    private function fakeIdUser(): SocialiteUser
    {
        return (new SocialiteUser)->setRaw([
            'sub' => '42',
            'name' => 'Robbin Thijssen',
            'email' => 'robbin@example.com',
            'applications' => ['cms'],
        ])->map([
            'id' => '42',
            'name' => 'Robbin Thijssen',
            'email' => 'robbin@example.com',
        ]);
    }

    private function mockSocialite(callable $configure): void
    {
        $provider = Mockery::mock(Provider::class);
        $configure($provider);

        Socialite::shouldReceive('driver')->with('thijssensoftware')->andReturn($provider);
    }

    public function test_the_redirect_route_starts_the_sso_flow(): void
    {
        $this->mockSocialite(fn ($provider) => $provider->shouldReceive('redirect')->andReturn(redirect('https://id.test/oauth/authorize')));

        $this->get(route('sso.redirect'))->assertRedirect('https://id.test/oauth/authorize');
    }

    public function test_it_provisions_and_logs_in_a_new_user(): void
    {
        $this->mockSocialite(fn ($provider) => $provider->shouldReceive('user')->andReturn($this->fakeIdUser()));

        $this->get(route('sso.callback'))->assertRedirect('/admin');

        $this->assertAuthenticated();
        $user = User::where('email', 'robbin@example.com')->firstOrFail();
        $this->assertSame('42', $user->idp_id);
    }

    public function test_it_links_an_existing_user_by_email(): void
    {
        $existing = User::factory()->create(['email' => 'robbin@example.com', 'idp_id' => null]);

        $this->mockSocialite(fn ($provider) => $provider->shouldReceive('user')->andReturn($this->fakeIdUser()));

        $this->get(route('sso.callback'))->assertRedirect('/admin');

        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame('42', $existing->fresh()->idp_id);
        $this->assertSame(1, User::count());
    }

    public function test_it_denies_a_user_without_access(): void
    {
        $this->mockSocialite(fn ($provider) => $provider->shouldReceive('user')->andThrow(new AccessDeniedException('nope')));

        $this->get(route('sso.callback'))->assertForbidden();

        $this->assertGuest();
    }

    public function test_the_remember_me_cookie_alone_signs_the_user_back_in(): void
    {
        $cookie = $this->rememberCookieFromSsoSignIn();

        $this->returnWithOnlyRememberCookie($cookie)->assertOk();

        $this->assertAuthenticatedAs(User::where('idp_id', '42')->sole());
    }

    public function test_the_remember_me_cookie_is_refused_once_id_signs_the_user_out(): void
    {
        config(['id-client.logout_secret' => 'test-logout-secret']);
        $cookie = $this->rememberCookieFromSsoSignIn();

        $body = json_encode(['event' => 'logout', 'sub' => '42', 'issued_at' => Carbon::now()->getTimestamp()], JSON_THROW_ON_ERROR);

        $this->call('POST', route('sso.logout'), server: [
            'HTTP_X_ID_SIGNATURE' => hash_hmac('sha256', $body, 'test-logout-secret'),
            'CONTENT_TYPE' => 'application/json',
        ], content: $body)->assertOk();

        $this->returnWithOnlyRememberCookie($cookie)->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    private function rememberCookieFromSsoSignIn(): string
    {
        $this->mockSocialite(fn ($provider) => $provider->shouldReceive('user')->andReturn($this->fakeIdUser()));

        $cookie = $this->get(route('sso.callback'))->getCookie(Auth::guard('web')->getRecallerName());

        $this->assertNotNull($cookie);

        return (string) $cookie->getValue();
    }

    /**
     * The same browser after its session has idled out: the remember-me cookie
     * is all it still has.
     */
    private function returnWithOnlyRememberCookie(string $cookie): TestResponse
    {
        $this->flushSession();
        Auth::forgetGuards();

        return $this->withCookie(Auth::guard('web')->getRecallerName(), $cookie)->get(route('admin.dashboard'));
    }
}
