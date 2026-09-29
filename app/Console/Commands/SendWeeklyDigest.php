<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\WeeklyDigest;
use App\Models\ContactSubmission;
use App\Models\PageView;
use App\Models\Profile;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:weekly';

    protected $description = 'Queue an email to the profile address summarising last week\'s traffic and unread enquiries';

    public function handle(): int
    {
        $email = Profile::current()->email;

        if (! $email) {
            $this->info('No profile email set, weekly digest skipped.');

            return self::SUCCESS;
        }

        // The Monday-to-Sunday week that has fully ended, so a rerun later in
        // the week reports the same figures as the scheduled run did.
        $end = now()->toImmutable()->startOfWeek(CarbonInterface::MONDAY);
        $start = $end->subWeek();

        $digest = new WeeklyDigest(
            period: $this->period($start, $end->subDay()),
            views: $this->pageViews($start, $end)->count(),
            previousViews: $this->pageViews($start->subWeek(), $start)->count(),
            topPaths: $this->topPaths($start, $end),
            topReferrers: $this->topReferrers($start, $end),
            unreadEnquiries: ContactSubmission::whereNull('read_at')->count(),
        );

        if ($digest->isQuiet()) {
            $this->info('Nothing to report for '.$digest->period.', weekly digest skipped.');

            return self::SUCCESS;
        }

        // A digest is a nudge, not a record: a failure is reported, and the
        // scheduled run still succeeds.
        try {
            Mail::to($email)->queue($digest);
        } catch (Throwable $e) {
            report($e);
            $this->error('Weekly digest could not be queued: '.$e->getMessage());

            return self::SUCCESS;
        }

        $this->info('Weekly digest queued for '.$digest->period.'.');

        return self::SUCCESS;
    }

    /**
     * @return Builder<PageView>
     */
    private function pageViews(CarbonImmutable $from, CarbonImmutable $until): Builder
    {
        return PageView::where('created_at', '>=', $from)->where('created_at', '<', $until);
    }

    private function period(CarbonImmutable $first, CarbonImmutable $last): string
    {
        $format = match (true) {
            $first->format('Y-m') === $last->format('Y-m') => 'j',
            $first->year === $last->year => 'j F',
            default => 'j F Y',
        };

        return $first->format($format).' to '.$last->format('j F Y');
    }

    /**
     * @return list<array{path: string, views: int}>
     */
    private function topPaths(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return array_values($this->pageViews($start, $end)
            ->selectRaw('path, count(*) as views')
            ->groupBy('path')
            ->orderByDesc('views')
            ->orderBy('path')
            ->limit(5)
            ->pluck('views', 'path')
            ->map(fn (mixed $views, mixed $path): array => ['path' => (string) $path, 'views' => $this->toInt($views)])
            ->all());
    }

    /**
     * @return list<array{host: string, views: int}>
     */
    private function topReferrers(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return array_values($this->pageViews($start, $end)
            ->selectRaw('referrer_host, count(*) as views')
            ->whereNotNull('referrer_host')
            ->groupBy('referrer_host')
            ->orderByDesc('views')
            ->orderBy('referrer_host')
            ->limit(5)
            ->pluck('views', 'referrer_host')
            ->map(fn (mixed $views, mixed $host): array => ['host' => (string) $host, 'views' => $this->toInt($views)])
            ->all());
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
