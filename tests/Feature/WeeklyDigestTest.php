<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\WeeklyDigest;
use App\Models\ContactSubmission;
use App\Models\PageView;
use App\Models\Profile;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class WeeklyDigestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A Monday morning, when the schedule runs it: the digest covers the
        // Monday-to-Sunday week that just ended, 21 to 27 September.
        $this->travelTo(Carbon::parse('2026-09-28 07:00:00'));

        Profile::current()->update(['email' => 'owner@example.com']);
        Mail::fake();
    }

    private function pageView(string $path, string $at, ?string $referrer = PageView::DIRECT): void
    {
        PageView::create(['path' => $path, 'referrer_host' => $referrer])
            ->forceFill(['created_at' => Carbon::parse($at)])
            ->save();
    }

    private function pageViews(int $count, string $path, string $at, ?string $referrer = PageView::DIRECT): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->pageView($path, $at, $referrer);
        }
    }

    private function enquiry(bool $read = false): void
    {
        ContactSubmission::create([
            'name' => 'Jane Client',
            'email' => 'jane@example.com',
            'message' => 'I would like to discuss a project.',
        ])->forceFill(['read_at' => $read ? now() : null])->save();
    }

    private function seedBusyWeek(): void
    {
        // The week being reported: 12 views over six paths.
        $this->pageViews(4, '/', '2026-09-21 00:00:00', 'linkedin.com');
        $this->pageViews(3, '/work', '2026-09-23 12:00:00', 'google.com');
        $this->pageViews(2, '/work/acme', '2026-09-25 09:00:00');
        $this->pageView('/nl', '2026-09-26 18:00:00', 'google.com');
        $this->pageView('/docs', '2026-09-27 23:59:59');
        $this->pageView('/work/tag/laravel', '2026-09-27 10:00:00', null);

        // The week before: 3 views.
        $this->pageViews(3, '/', '2026-09-14 08:00:00', 'github.com');

        // Outside both weeks, on either side: never counted.
        $this->pageViews(5, '/cv.pdf', '2026-09-13 23:59:59');
        $this->pageViews(5, '/cv.pdf', '2026-09-28 06:00:00');

        $this->enquiry();
        $this->enquiry();
        $this->enquiry(read: true);
    }

    public function test_it_queues_the_figures_for_the_week_that_just_ended_to_the_profile_address(): void
    {
        $this->seedBusyWeek();

        $this->artisan('digest:weekly')
            ->expectsOutputToContain('Weekly digest queued')
            ->assertExitCode(0);

        Mail::assertQueuedCount(1);
        Mail::assertQueued(WeeklyDigest::class, function (WeeklyDigest $mail): bool {
            $this->assertTrue($mail->hasTo('owner@example.com'));
            $this->assertSame('21 to 27 September 2026', $mail->period);
            $this->assertSame(12, $mail->views);
            $this->assertSame(3, $mail->previousViews);
            $this->assertSame([
                ['path' => '/', 'views' => 4],
                ['path' => '/work', 'views' => 3],
                ['path' => '/work/acme', 'views' => 2],
                ['path' => '/docs', 'views' => 1],
                ['path' => '/nl', 'views' => 1],
            ], $mail->topPaths);
            $this->assertSame([
                ['host' => 'google.com', 'views' => 4],
                ['host' => 'linkedin.com', 'views' => 4],
                ['host' => 'direct', 'views' => 3],
            ], $mail->topReferrers);
            $this->assertSame(2, $mail->unreadEnquiries);

            return true;
        });
    }

    public function test_the_email_renders_the_figures(): void
    {
        $this->seedBusyWeek();

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigest::class, function (WeeklyDigest $mail): bool {
            $mail->assertHasSubject('Weekly digest: 21 to 27 September 2026');
            $mail->assertSeeInOrderInHtml(['12 page views', 'against 3 the week before']);
            $mail->assertSeeInOrderInHtml(['/', '4', '/work', '3', '/work/acme', '2', '/docs', '1', '/nl', '1']);
            $mail->assertSeeInOrderInHtml(['google.com', '4', 'linkedin.com', '4', 'direct', '3']);
            $mail->assertSeeInHtml('2 unread enquiries');
            $mail->assertSeeInHtml(route('admin.contact-submissions.index', ['state' => 'unread']), false);

            return true;
        });
    }

    public function test_a_week_spanning_two_months_names_both(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 07:00:00'));
        $this->pageView('/', '2026-10-01 10:00:00');

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigest::class, fn (WeeklyDigest $mail): bool => $mail->period === '28 September to 4 October 2026');
    }

    public function test_a_quiet_week_sends_nothing(): void
    {
        // Activity the week before and an enquiry already read do not make the
        // week that just ended any less quiet.
        $this->pageViews(3, '/', '2026-09-14 08:00:00');
        $this->enquiry(read: true);

        $this->artisan('digest:weekly')
            ->expectsOutputToContain('Nothing to report')
            ->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

    public function test_unread_enquiries_alone_are_worth_a_digest(): void
    {
        $this->enquiry();

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigest::class, function (WeeklyDigest $mail): bool {
            $this->assertSame(0, $mail->views);
            $this->assertSame(1, $mail->unreadEnquiries);
            $mail->assertSeeInHtml('1 unread enquiry');

            return true;
        });
    }

    public function test_views_alone_are_worth_a_digest_and_leave_out_the_enquiries_section(): void
    {
        $this->pageView('/', '2026-09-22 10:00:00');

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigest::class, function (WeeklyDigest $mail): bool {
            $mail->assertSeeInHtml('1 page view');
            $mail->assertDontSeeInHtml('unread');

            return true;
        });
    }

    public function test_it_is_queued_rather_than_sent_inline(): void
    {
        $this->pageView('/', '2026-09-22 10:00:00');

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertNothingSent();
        Mail::assertQueued(WeeklyDigest::class);
    }

    public function test_without_a_profile_address_it_sends_nothing(): void
    {
        Profile::current()->update(['email' => null]);
        $this->pageView('/', '2026-09-22 10:00:00');

        $this->artisan('digest:weekly')
            ->expectsOutputToContain('No profile email')
            ->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

    public function test_a_send_failure_is_reported_and_never_fails_the_scheduled_run(): void
    {
        Exceptions::fake();
        Mail::shouldReceive('to')->andThrow(new RuntimeException('Queue unavailable'));
        $this->pageView('/', '2026-09-22 10:00:00');

        $this->artisan('digest:weekly')
            ->expectsOutputToContain('Weekly digest could not be queued: Queue unavailable')
            ->assertExitCode(0);

        Exceptions::assertReported(fn (RuntimeException $e): bool => $e->getMessage() === 'Queue unavailable');
    }

    public function test_it_is_scheduled_for_monday_morning(): void
    {
        $this->artisan('schedule:list')->assertExitCode(0);

        $digest = collect(app(Schedule::class)->events())
            ->filter(fn ($event): bool => str_contains((string) $event->command, 'digest:weekly'));

        $this->assertCount(1, $digest);
        $this->assertSame('0 7 * * 1', $digest->sole()->expression);
    }
}
