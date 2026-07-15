<?php

namespace App\Console\Commands;

use App\Models\PageView;
use App\Models\PageViewTotal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrunePageViews extends Command
{
    protected $signature = 'page-views:prune {--days=90 : Roll up and delete page_view rows older than this many days}';

    protected $description = 'Roll old page_view rows into per-path totals, then delete them to bound table growth';

    public function handle(): int
    {
        // Floored at 30: the dashboard sparkline reads the last 30 days of raw
        // page_view rows, so retention must never drop below that window.
        $days = max(30, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = DB::transaction(function () use ($cutoff): int {
            $counts = PageView::where('created_at', '<', $cutoff)
                ->groupBy('path')
                ->selectRaw('path, count(*) as aggregate')
                ->pluck('aggregate', 'path');

            foreach ($counts as $path => $count) {
                $total = PageViewTotal::firstOrNew(['path' => $path]);
                $total->views = ($total->views ?? 0) + (int) $count;
                $total->save();
            }

            return PageView::where('created_at', '<', $cutoff)->delete();
        });

        $this->info("Rolled up and pruned {$deleted} page_view rows older than {$days} days.");

        return self::SUCCESS;
    }
}
