<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Portal\IdPortalClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IdPortalClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.thijssensoftware.base_url', 'https://id.example.test');
        Config::set('services.thijssensoftware.client_id', 'client-id');
        Config::set('services.thijssensoftware.client_secret', 'client-secret');
        Config::set('services.thijssensoftware.slug', 'cms');
    }

    public function test_a_successful_fetch_returns_the_apps(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'token', 'expires_in' => 600]),
            '*/api/portal/apps' => Http::response([
                'applications' => [
                    ['slug' => 'cms', 'name' => 'CMS', 'initials' => 'CM', 'accent' => null, 'launch_url' => 'https://cms.test'],
                ],
                'categories' => [],
            ]),
        ]);

        $result = app(IdPortalClient::class)->appsFor(User::factory()->create());

        $this->assertCount(1, $result['apps']);
        $this->assertTrue($result['apps'][0]['current']);
    }

    public function test_a_timeout_on_the_token_call_fails_soft(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));

        $result = app(IdPortalClient::class)->appsFor(User::factory()->create());

        $this->assertSame(['apps' => [], 'categories' => []], $result);
    }

    public function test_a_timeout_on_the_apps_call_fails_soft_and_is_not_cached(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'token', 'expires_in' => 600]),
            '*/api/portal/apps' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
        ]);

        $result = app(IdPortalClient::class)->appsFor($user);

        $this->assertSame(['apps' => [], 'categories' => []], $result);
        $this->assertNull(Cache::get('portal-apps:v2:'.sha1($user->email)));
    }

    public function test_a_timeout_does_not_break_the_admin_dashboard(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));

        $this->actingAs(User::factory()->create())->get('/admin')->assertOk();
    }
}
