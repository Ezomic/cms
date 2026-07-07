<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactSubmission;
use App\Models\PageView;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
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

        $topPaths = PageView::select('path', DB::raw('count(*) as views'))
            ->groupBy('path')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'projectCount' => Project::count(),
            'skillCount' => Skill::count(),
            'testimonialCount' => Testimonial::count(),
            'pageViewCount' => PageView::count(),
            'activity' => ActivityLog::with('user')->latest()->take(8)->get(),
            'sparkline' => $sparkline,
            'topPaths' => $topPaths,
            'contactCount' => ContactSubmission::count(),
        ]);
    }
}
