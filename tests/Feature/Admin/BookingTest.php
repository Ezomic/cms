<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function booking(array $overrides = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'uuid' => (string) Str::uuid(),
            'type' => 'Intro call',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addMinutes(30),
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_phone' => '+31 6 12345678',
            'guest_note' => 'See you then.',
            'guest_timezone' => 'Europe/Amsterdam',
            'locale' => 'nl',
            'booked_at' => now(),
        ], $overrides));
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $booking = $this->booking();

        $this->get('/admin/bookings')->assertRedirect('/admin/login');
        $this->post("/admin/bookings/{$booking->id}/read")->assertRedirect('/admin/login');
        $this->post("/admin/bookings/{$booking->id}/unread")->assertRedirect('/admin/login');

        $this->assertNull($booking->fresh()?->read_at);
    }

    public function test_admin_sees_a_booking_with_every_field(): void
    {
        $this->travelTo('2026-11-13 12:00:00');
        $this->booking([
            'starts_at' => '2026-12-01 13:00:00',
            'ends_at' => '2026-12-01 13:30:00',
        ]);

        $this->actingAs(User::factory()->create())->get('/admin/bookings')->assertInertia(
            fn (Assert $page) => $page
                ->component('Bookings/Index')
                ->where('bookings.data.0.name', 'Jane Doe')
                ->where('bookings.data.0.email', 'jane@example.com')
                ->where('bookings.data.0.phone', '+31 6 12345678')
                ->where('bookings.data.0.note', 'See you then.')
                ->where('bookings.data.0.type', 'Intro call')
                ->where('bookings.data.0.starts', 'Tue 1 Dec 2026, 14:00')
                ->where('bookings.data.0.ends', '14:30')
                ->where('bookings.data.0.guest_timezone', 'Europe/Amsterdam')
                ->where('bookings.data.0.is_cancelled', false)
                ->where('bookings.data.0.is_past', false)
                ->where('bookings.data.0.is_read', false)
                ->where('bookings.data.0.received', '0 seconds ago')
        );
    }

    public function test_start_time_follows_amsterdam_summer_time(): void
    {
        $this->booking(['starts_at' => '2027-07-01 13:00:00', 'ends_at' => '2027-07-01 13:30:00']);

        $this->actingAs(User::factory()->create())->get('/admin/bookings')->assertInertia(
            fn (Assert $page) => $page->where('bookings.data.0.starts', 'Thu 1 Jul 2027, 15:00')
        );
    }

    public function test_cancelled_booking_is_shown_as_cancelled(): void
    {
        $this->booking(['cancelled_at' => now()]);

        $this->actingAs(User::factory()->create())->get('/admin/bookings')->assertInertia(
            fn (Assert $page) => $page->where('bookings.data.0.is_cancelled', true)
        );
    }

    public function test_bookings_are_listed_newest_first(): void
    {
        $this->travelTo(now()->subDay(), fn () => $this->booking(['guest_name' => 'Older']));
        $this->booking(['guest_name' => 'Newer']);

        $this->actingAs(User::factory()->create())->get('/admin/bookings')->assertInertia(
            fn (Assert $page) => $page
                ->where('bookings.data.0.name', 'Newer')
                ->where('bookings.data.1.name', 'Older')
        );
    }

    public function test_filter_splits_upcoming_past_and_cancelled(): void
    {
        $this->booking(['guest_name' => 'Upcoming']);
        $this->booking(['guest_name' => 'Past', 'starts_at' => now()->subWeek(), 'ends_at' => now()->subWeek()->addMinutes(30)]);
        $this->booking(['guest_name' => 'Cancelled', 'cancelled_at' => now()]);

        $user = User::factory()->create();

        foreach (['upcoming' => 'Upcoming', 'past' => 'Past', 'cancelled' => 'Cancelled'] as $state => $name) {
            $this->actingAs($user)->get("/admin/bookings?state={$state}")->assertInertia(
                fn (Assert $page) => $page
                    ->where('filters.state', $state)
                    ->has('bookings.data', 1)
                    ->where('bookings.data.0.name', $name)
            );
        }

        $this->actingAs($user)->get('/admin/bookings?state=nonsense')->assertInertia(
            fn (Assert $page) => $page
                ->where('filters.state', 'all')
                ->has('bookings.data', 3)
                ->where('totals', ['all' => 3, 'upcoming' => 1, 'past' => 1, 'cancelled' => 1])
        );
    }

    public function test_html_in_name_and_note_reaches_the_page_escaped(): void
    {
        $this->booking([
            'guest_name' => '<script>alert("name")</script>',
            'guest_note' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->actingAs(User::factory()->create())->get('/admin/bookings');

        $response->assertOk();
        $response->assertDontSee('<script>alert("name")</script>', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertInertia(
            fn (Assert $page) => $page
                ->where('bookings.data.0.name', '<script>alert("name")</script>')
                ->where('bookings.data.0.note', '<img src=x onerror=alert(1)>')
        );
    }

    public function test_admin_can_mark_a_booking_read_and_unread(): void
    {
        $user = User::factory()->create();
        $booking = $this->booking();

        $this->actingAs($user)->post("/admin/bookings/{$booking->id}/read")->assertRedirect();
        $this->assertNotNull($booking->fresh()?->read_at);

        $this->actingAs($user)->post("/admin/bookings/{$booking->id}/unread")->assertRedirect();
        $this->assertNull($booking->fresh()?->read_at);
    }

    public function test_booking_data_cannot_be_changed_or_deleted_from_the_admin(): void
    {
        $user = User::factory()->create();
        $booking = $this->booking();

        $this->actingAs($user)->delete("/admin/bookings/{$booking->id}")->assertStatus(405);
        $this->actingAs($user)->put("/admin/bookings/{$booking->id}", ['guest_name' => 'Changed'])->assertStatus(405);
        $this->actingAs($user)->post("/admin/bookings/{$booking->id}/read", ['guest_name' => 'Changed', 'cancelled_at' => now()]);

        $fresh = $booking->fresh();

        $this->assertSame('Jane Doe', $fresh?->guest_name);
        $this->assertNull($fresh?->cancelled_at);
    }

    public function test_sidebar_counts_unread_bookings_apart_from_unread_messages(): void
    {
        $this->booking();
        $this->booking();
        $this->booking(['read_at' => now()]);
        ContactSubmission::create(['name' => 'Jane', 'email' => 'jane@example.com', 'message' => 'Hi']);

        $this->actingAs(User::factory()->create())->get('/admin/bookings')->assertInertia(
            fn (Assert $page) => $page
                ->where('counts.unread', 1)
                ->where('counts.unreadBookings', 2)
        );
    }
}
