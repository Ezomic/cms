<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactSubmission;
use App\Models\PageView;
use App\Models\PageViewTotal;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Services\ContentCompleteness;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $dailyViews = PageView::select(
            DB::raw('date(created_at) as day'),
            DB::raw('count(*) as views')
        )
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('views', 'day');

        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $sparkline = $days->map(function (string $d) use ($dailyViews): int {
            $views = $dailyViews->get($d, 0);

            return is_numeric($views) ? (int) $views : 0;
        })->values();

        return Inertia::render('Dashboard', [
            'projectCount' => Project::count(),
            'skillCount' => Skill::count(),
            'testimonialCount' => Testimonial::count(),
            'pageViewCount' => PageView::count() + (int) PageViewTotal::sum('views'),
            'contactCount' => ContactSubmission::count(),
            'unreadCount' => ContactSubmission::whereNull('read_at')->count(),
            'sparkline' => $sparkline,
            'topPaths' => $this->topPaths(),
            'topReferrers' => $this->topReferrers(),
            'contentGapCount' => app(ContentCompleteness::class)->count(),
            'activity' => ActivityLog::with('user')->latest()->take(8)->get()->map(function (ActivityLog $log) {
                $subject = class_basename($log->subject_type);

                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'subject' => $subject,
                    // Hide a label that just repeats the subject type (e.g. "Profile").
                    'label' => $log->subject_label === $subject ? null : $log->subject_label,
                    'user' => $log->user?->name,
                    'when' => $log->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * All-time top paths, combining live page_view rows with the per-path
     * totals rolled up from pruned history.
     *
     * @return Collection<int, array{path: string, views: int}>
     */
    private function topPaths(): Collection
    {
        $live = PageView::selectRaw('path, count(*) as views')->groupBy('path')->pluck('views', 'path');
        $rolled = PageViewTotal::pluck('views', 'path');

        return collect($live)->keys()->merge($rolled->keys())->unique()
            ->mapWithKeys(fn (string $path): array => [
                $path => $this->toInt($live->get($path, 0)) + $this->toInt($rolled->get($path, 0)),
            ])
            ->sortDesc()
            ->take(5)
            ->map(fn (int $views, string $path): array => ['path' => $path, 'views' => $views])
            ->values();
    }

    /**
     * Top referrer hosts over the last 30 days.
     *
     * Deliberately a rolling window rather than all-time: page-views:prune
     * rolls rows older than 90 days into page_view_totals, which is keyed by
     * path only, so referrers do not survive pruning and an all-time figure
     * would quietly decay.
     *
     * @return Collection<int, array{host: string, views: int}>
     */
    private function topReferrers(): Collection
    {
        $counts = PageView::selectRaw('referrer_host, count(*) as views')
            ->whereNotNull('referrer_host')
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('referrer_host')
            ->orderByDesc('views')
            ->limit(5)
            ->pluck('views', 'referrer_host');

        return collect($counts)
            ->map(fn (mixed $views, mixed $host): array => [
                'host' => (string) $host,
                'views' => $this->toInt($views),
            ])
            ->values();
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
