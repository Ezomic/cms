<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PageView;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChronosBookingWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/webhooks/chronos/bookings';

    private const SECRET = 'test-webhook-secret';

    private const UUID = '0193a6c2-7b1e-7c4a-9f0e-3d2b1a4c5e6f';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.chronos.webhook_secret' => self::SECRET]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(string $event = 'booking.created', array $overrides = []): array
    {
        return [
            'event' => $event,
            'booking' => array_merge([
                'uuid' => self::UUID,
                'type' => 'Intro call',
                'starts_at' => '2026-12-01T13:00:00Z',
                'ends_at' => '2026-12-01T13:30:00Z',
                'guest_name' => 'Jane Doe',
                'guest_email' => 'jane@example.com',
                'guest_phone' => '+31 6 12345678',
                'guest_note' => 'Looking forward to it.',
                'guest_timezone' => 'Europe/Amsterdam',
                'locale' => 'nl',
                'cancelled_at' => $event === 'booking.cancelled' ? '2026-11-20T09:15:00Z' : null,
                'created_at' => '2026-11-13T10:00:00Z',
            ], $overrides),
        ];
    }

    /**
     * Sends the exact bytes that were signed, the way Chronos does, so the
     * signature check is exercised against the raw request body.
     *
     * @param  array<string, string|null>  $headers  null removes a header
     */
    private function deliver(string $body, array $headers = [], ?string $secret = self::SECRET, ?int $timestamp = null): TestResponse
    {
        $timestamp ??= now()->getTimestamp();

        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CHRONOS_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_CHRONOS_SIGNATURE' => hash_hmac('sha256', $timestamp.'.'.$body, (string) $secret),
        ];

        foreach ($headers as $name => $value) {
            $key = 'HTTP_'.strtoupper(str_replace('-', '_', $name));

            if ($value === null) {
                unset($server[$key]);
            } else {
                $server[$key] = $value;
            }
        }

        return $this->call('POST', self::URL, [], [], [], $server, $body);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function send(array $payload): TestResponse
    {
        return $this->deliver((string) json_encode($payload));
    }

    public function test_a_signed_booking_is_stored_unread(): void
    {
        $this->send($this->payload())->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame(self::UUID, $booking->uuid);
        $this->assertSame('Intro call', $booking->type);
        $this->assertSame('2026-12-01 13:00:00', $booking->starts_at->toDateTimeString());
        $this->assertSame('2026-12-01 13:30:00', $booking->ends_at->toDateTimeString());
        $this->assertSame('Jane Doe', $booking->guest_name);
        $this->assertSame('jane@example.com', $booking->guest_email);
        $this->assertSame('+31 6 12345678', $booking->guest_phone);
        $this->assertSame('Looking forward to it.', $booking->guest_note);
        $this->assertSame('Europe/Amsterdam', $booking->guest_timezone);
        $this->assertSame('nl', $booking->locale);
        $this->assertSame('2026-11-13 10:00:00', $booking->booked_at->toDateTimeString());
        $this->assertNull($booking->cancelled_at);
        $this->assertNull($booking->read_at);
    }

    public function test_the_response_has_no_body(): void
    {
        $this->assertSame('', $this->send($this->payload())->getContent());
    }

    public function test_missing_signature_is_rejected(): void
    {
        $this->deliver((string) json_encode($this->payload()), ['X-Chronos-Signature' => null])->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_empty_signature_is_rejected(): void
    {
        $this->deliver((string) json_encode($this->payload()), ['X-Chronos-Signature' => ''])->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_missing_timestamp_is_rejected(): void
    {
        $this->deliver((string) json_encode($this->payload()), ['X-Chronos-Timestamp' => null])->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_signature_made_with_the_wrong_secret_is_rejected(): void
    {
        $this->deliver((string) json_encode($this->payload()), secret: 'some-other-secret')->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_signature_over_a_different_body_is_rejected(): void
    {
        $timestamp = now()->getTimestamp();
        $signedBody = (string) json_encode($this->payload());
        $sentBody = (string) json_encode($this->payload(overrides: ['guest_email' => 'attacker@example.com']));

        $this->deliver($sentBody, [
            'X-Chronos-Signature' => hash_hmac('sha256', $timestamp.'.'.$signedBody, self::SECRET),
        ], timestamp: $timestamp)->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_signature_over_the_body_without_the_timestamp_is_rejected(): void
    {
        $body = (string) json_encode($this->payload());

        $this->deliver($body, ['X-Chronos-Signature' => hash_hmac('sha256', $body, self::SECRET)])->assertUnauthorized();
    }

    public function test_uppercase_signature_is_rejected(): void
    {
        $timestamp = now()->getTimestamp();
        $body = (string) json_encode($this->payload());

        $this->deliver($body, [
            'X-Chronos-Signature' => strtoupper(hash_hmac('sha256', $timestamp.'.'.$body, self::SECRET)),
        ], timestamp: $timestamp)->assertUnauthorized();
    }

    public function test_stale_timestamp_is_rejected(): void
    {
        $this->deliver((string) json_encode($this->payload()), timestamp: now()->getTimestamp() - 301)->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_future_timestamp_is_rejected(): void
    {
        $this->deliver((string) json_encode($this->payload()), timestamp: now()->getTimestamp() + 301)->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    public function test_timestamp_at_the_edge_of_the_window_is_accepted(): void
    {
        $this->deliver((string) json_encode($this->payload()), timestamp: now()->getTimestamp() - 300)->assertNoContent();
    }

    #[DataProvider('malformedTimestamps')]
    public function test_malformed_timestamp_is_rejected(string $timestamp): void
    {
        $body = (string) json_encode($this->payload());

        $this->deliver($body, [
            'X-Chronos-Timestamp' => $timestamp,
            'X-Chronos-Signature' => hash_hmac('sha256', $timestamp.'.'.$body, self::SECRET),
        ])->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function malformedTimestamps(): array
    {
        return [
            'words' => ['yesterday'],
            'iso date' => ['2026-11-13T10:00:00Z'],
            'float' => ['1794650000.5'],
            'negative' => ['-1794650000'],
            'scientific' => ['1e9'],
            'far too long' => [str_repeat('9', 40)],
        ];
    }

    #[DataProvider('emptySecrets')]
    public function test_an_unconfigured_secret_never_validates(mixed $configured): void
    {
        config(['services.chronos.webhook_secret' => $configured]);

        $this->deliver((string) json_encode($this->payload()), secret: '')->assertUnauthorized();

        $this->assertSame(0, Booking::count());
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function emptySecrets(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'whitespace' => ['   '],
        ];
    }

    public function test_unknown_event_is_a_validation_failure(): void
    {
        $this->send($this->payload('booking.rescheduled'))->assertStatus(422);

        $this->assertSame(0, Booking::count());
    }

    public function test_validation_failure_is_422_without_an_accept_header(): void
    {
        $response = $this->send(['event' => 'booking.created']);

        $response->assertStatus(422);
        $this->assertFalse($response->isRedirect());
    }

    public function test_body_that_is_not_json_is_a_validation_failure(): void
    {
        $this->deliver('not json at all')->assertStatus(422);
    }

    #[DataProvider('invalidBookingFields')]
    public function test_invalid_booking_field_is_a_validation_failure(string $field, mixed $value): void
    {
        $payload = $this->payload();

        if ($value === '__missing__') {
            unset($payload['booking'][$field]);
        } else {
            $payload['booking'][$field] = $value;
        }

        $this->send($payload)->assertStatus(422);

        $this->assertSame(0, Booking::count());
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function invalidBookingFields(): array
    {
        return [
            'missing uuid' => ['uuid', '__missing__'],
            'null uuid' => ['uuid', null],
            'non-uuid uuid' => ['uuid', 'booking-42'],
            'array uuid' => ['uuid', [self::UUID]],
            'missing type' => ['type', '__missing__'],
            'very long type' => ['type', str_repeat('a', 256)],
            'missing name' => ['guest_name', '__missing__'],
            'very long name' => ['guest_name', str_repeat('a', 256)],
            'array name' => ['guest_name', ['Jane']],
            'missing email' => ['guest_email', '__missing__'],
            'malformed email' => ['guest_email', 'not-an-email'],
            'very long phone' => ['guest_phone', str_repeat('1', 256)],
            'very long note' => ['guest_note', str_repeat('a', 10001)],
            'missing timezone' => ['guest_timezone', '__missing__'],
            'missing locale' => ['locale', '__missing__'],
            'missing starts_at' => ['starts_at', '__missing__'],
            'starts_at without the Z' => ['starts_at', '2026-12-01 13:00:00'],
            'starts_at with an offset' => ['starts_at', '2026-12-01T13:00:00+01:00'],
            'starts_at that is not a date' => ['starts_at', 'next tuesday'],
            'starts_at on an impossible day' => ['starts_at', '2026-02-31T13:00:00Z'],
            'malformed ends_at' => ['ends_at', '01-12-2026'],
            'malformed created_at' => ['created_at', '1794650000'],
            'malformed cancelled_at' => ['cancelled_at', 'yes'],
        ];
    }

    public function test_null_phone_and_note_are_stored_as_null(): void
    {
        $this->send($this->payload(overrides: ['guest_phone' => null, 'guest_note' => null]))->assertNoContent();

        $booking = Booking::sole();

        $this->assertNull($booking->guest_phone);
        $this->assertNull($booking->guest_note);
    }

    public function test_unicode_and_html_are_stored_as_sent(): void
    {
        $name = 'Zoë Müller 山田 👋';
        $note = "<script>alert('x')</script>\n<b>Tot dan</b> & bedankt";

        $this->send($this->payload(overrides: ['guest_name' => $name, 'guest_note' => $note]))->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame($name, $booking->guest_name);
        $this->assertSame($note, $booking->guest_note);
    }

    public function test_values_are_trimmed_before_storing(): void
    {
        $this->send($this->payload(overrides: ['guest_name' => "  Jane Doe \n", 'guest_note' => '  hello  ']))->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame('Jane Doe', $booking->guest_name);
        $this->assertSame('hello', $booking->guest_note);
    }

    public function test_longest_allowed_name_and_note_are_stored(): void
    {
        $this->send($this->payload(overrides: [
            'guest_name' => str_repeat('é', 255),
            'guest_note' => str_repeat('é', 10000),
        ]))->assertNoContent();

        $this->assertSame(10000, mb_strlen((string) Booking::sole()->guest_note));
    }

    public function test_the_same_event_delivered_twice_stores_one_row(): void
    {
        $this->send($this->payload())->assertNoContent();
        $this->send($this->payload())->assertNoContent();

        $this->assertSame(1, Booking::count());
    }

    public function test_a_redelivered_created_event_does_not_make_a_read_booking_unread(): void
    {
        $this->send($this->payload());
        Booking::sole()->forceFill(['read_at' => now()])->save();

        $this->send($this->payload())->assertNoContent();

        $this->assertNotNull(Booking::sole()->read_at);
    }

    public function test_a_cancellation_marks_the_booking_cancelled(): void
    {
        $this->send($this->payload());
        $this->send($this->payload('booking.cancelled'))->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame('2026-11-20 09:15:00', $booking->cancelled_at?->toDateTimeString());
    }

    public function test_a_cancellation_makes_a_read_booking_unread_again(): void
    {
        $this->send($this->payload());
        Booking::sole()->forceFill(['read_at' => now()])->save();

        $this->send($this->payload('booking.cancelled'))->assertNoContent();

        $this->assertNull(Booking::sole()->read_at);
    }

    public function test_a_redelivered_cancellation_does_not_make_it_unread_a_second_time(): void
    {
        $this->send($this->payload('booking.cancelled'));
        Booking::sole()->forceFill(['read_at' => now()])->save();

        $this->send($this->payload('booking.cancelled'))->assertNoContent();

        $booking = Booking::sole();

        $this->assertNotNull($booking->read_at);
        $this->assertSame('2026-11-20 09:15:00', $booking->cancelled_at?->toDateTimeString());
    }

    public function test_a_cancellation_for_an_unknown_booking_creates_it_cancelled(): void
    {
        $this->send($this->payload('booking.cancelled'))->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame('Jane Doe', $booking->guest_name);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertNull($booking->read_at);
    }

    public function test_a_cancellation_without_a_cancelled_at_is_still_cancelled(): void
    {
        $this->send($this->payload('booking.cancelled', ['cancelled_at' => null]))->assertNoContent();

        $this->assertNotNull(Booking::sole()->cancelled_at);
    }

    public function test_a_created_event_after_a_cancellation_does_not_uncancel(): void
    {
        $this->send($this->payload('booking.cancelled'));
        $this->send($this->payload('booking.created'))->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame('2026-11-20 09:15:00', $booking->cancelled_at?->toDateTimeString());
    }

    public function test_a_created_event_that_already_carries_a_cancellation_is_stored_cancelled(): void
    {
        $this->send($this->payload('booking.created', ['cancelled_at' => '2026-11-20T09:15:00Z']))->assertNoContent();

        $this->assertNotNull(Booking::sole()->cancelled_at);
    }

    public function test_the_same_uuid_in_another_case_is_the_same_booking(): void
    {
        $this->send($this->payload());
        $this->send($this->payload('booking.cancelled', ['uuid' => strtoupper(self::UUID)]))->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame(self::UUID, $booking->uuid);
        $this->assertNotNull($booking->cancelled_at);
    }

    public function test_a_signed_json_body_is_read_without_a_content_type_header(): void
    {
        $body = (string) json_encode($this->payload(overrides: ['guest_name' => '  Jane Doe  ', 'guest_phone' => '  ']));
        $timestamp = now()->getTimestamp();

        $this->call('POST', self::URL, [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
            'HTTP_X_CHRONOS_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_CHRONOS_SIGNATURE' => hash_hmac('sha256', $timestamp.'.'.$body, self::SECRET),
        ], $body)->assertNoContent();

        $booking = Booking::sole();

        $this->assertSame('Jane Doe', $booking->guest_name);
        $this->assertNull($booking->guest_phone);
    }

    public function test_unsigned_query_parameters_never_reach_the_booking(): void
    {
        $body = (string) json_encode(['event' => 'booking.created']);
        $timestamp = now()->getTimestamp();
        $query = http_build_query(['booking' => $this->payload()['booking']]);

        $this->call('POST', self::URL.'?'.$query, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_CHRONOS_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_CHRONOS_SIGNATURE' => hash_hmac('sha256', $timestamp.'.'.$body, self::SECRET),
        ], $body)->assertStatus(422);

        $this->assertSame(0, Booking::count());
    }

    public function test_get_requests_fall_through_to_the_404_page(): void
    {
        $this->get(self::URL)->assertNotFound();
    }

    public function test_there_is_no_dutch_twin(): void
    {
        $this->assertFalse(Route::has('nl.webhooks.chronos.bookings'));

        // Only the GET fallback matches the path, so a POST is a 405.
        $this->call('POST', '/nl'.self::URL)->assertStatus(405);
    }

    public function test_the_route_runs_without_session_cookies_or_csrf(): void
    {
        $route = Route::getRoutes()->getByName('webhooks.chronos.bookings');

        $this->assertNotNull($route);

        $middleware = Route::gatherRouteMiddleware($route);

        $this->assertNotContains(StartSession::class, $middleware);
        $this->assertNotContains(EncryptCookies::class, $middleware);
        $this->assertNotContains(PreventRequestForgery::class, $middleware);
        $this->assertContains('Illuminate\Routing\Middleware\ThrottleRequests:chronos-webhook', $middleware);
    }

    public function test_a_delivery_sets_no_cookie_and_records_no_page_view(): void
    {
        $response = $this->send($this->payload());

        $this->assertSame([], $response->headers->getCookies());
        $this->assertSame(0, PageView::count());
    }

    public function test_deliveries_are_throttled(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->deliver('{}', ['X-Chronos-Signature' => 'wrong'])->assertUnauthorized();
        }

        $this->deliver('{}', ['X-Chronos-Signature' => 'wrong'])->assertStatus(429);
    }
}
