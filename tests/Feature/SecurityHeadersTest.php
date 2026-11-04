<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_public_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), browsing-topics=()');
    }

    public function test_a_baseline_content_security_policy_is_sent_without_script_or_style_directives(): void
    {
        $expected = "object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'";

        foreach (['/', '/nl', '/work', '/docs', '/admin/login'] as $path) {
            $response = $this->get($path);

            $response->assertHeader('Content-Security-Policy', $expected);
            $this->assertStringNotContainsString('script-src', (string) $response->headers->get('Content-Security-Policy'));
            $this->assertStringNotContainsString('style-src', (string) $response->headers->get('Content-Security-Policy'));
        }
    }

    public function test_the_policy_is_also_sent_on_the_admin_inertia_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
    }

    public function test_hsts_header_is_only_sent_over_https(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}
