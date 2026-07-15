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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
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
        $sparkline = $days->map(fn ($d) => $dailyViews->get($d, 0));

        return view('admin.dashboard', [
            'projectCount' => Project::count(),
            'skillCount' => Skill::count(),
            'testimonialCount' => Testimonial::count(),
            'pageViewCount' => PageView::count() + (int) PageViewTotal::sum('views'),
            'activity' => ActivityLog::with('user')->latest()->take(8)->get(),
            'sparkline' => $sparkline,
            'topPaths' => $this->topPaths(),
            'contactCount' => ContactSubmission::count(),
        ]);
    }

    /**
     * All-time top paths, combining live page_view rows with the per-path
     * totals rolled up from pruned history.
     *
     * @return Collection<int, object{path: string, views: int}&\stdClass>
     */
    private function topPaths(): Collection
    {
        $live = PageView::selectRaw('path, count(*) as views')->groupBy('path')->pluck('views', 'path');
        $rolled = PageViewTotal::pluck('views', 'path');

        return collect($live)->keys()->merge($rolled->keys())->unique()
            ->mapWithKeys(fn (string $path): array => [
                $path => (int) $live->get($path, 0) + (int) $rolled->get($path, 0),
            ])
            ->sortDesc()
            ->take(5)
            ->map(fn (int $views, string $path) => (object) ['path' => $path, 'views' => $views])
            ->values();
    }
}
