<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\PageViewTotal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrunePageViewsTest extends TestCase
{
    use RefreshDatabase;

    private function pageViewAgedDays(string $path, int $days): void
    {
        PageView::create(['path' => $path])
            ->forceFill(['created_at' => now()->subDays($days)])
            ->save();
    }

    public function test_it_rolls_up_old_rows_into_totals_then_deletes_them(): void
    {
        $this->pageViewAgedDays('/', 120);
        $this->pageViewAgedDays('/', 100);
        $this->pageViewAgedDays('/', 95);
        $this->pageViewAgedDays('/work', 100);
        $this->pageViewAgedDays('/work', 100);
        $this->pageViewAgedDays('/', 5); // within window, must survive

        $this->artisan('page-views:prune', ['--days' => 90])
            ->expectsOutputToContain('pruned 5 page_view rows')
            ->assertExitCode(0);

        $this->assertSame(1, PageView::count());
        $this->assertSame(3, (int) PageViewTotal::firstWhere('path', '/')->views);
        $this->assertSame(2, (int) PageViewTotal::firstWhere('path', '/work')->views);
    }

    public function test_it_keeps_rows_within_the_window(): void
    {
        $this->pageViewAgedDays('/', 10);
        $this->pageViewAgedDays('/blog', 45);

        $this->artisan('page-views:prune', ['--days' => 90])
            ->expectsOutputToContain('pruned 0 page_view rows')
            ->assertExitCode(0);

        $this->assertSame(2, PageView::count());
        $this->assertSame(0, PageViewTotal::count());
    }

    public function test_a_second_run_accumulates_onto_existing_totals(): void
    {
        $this->pageViewAgedDays('/', 100);
        $this->artisan('page-views:prune', ['--days' => 90])->assertExitCode(0);

        $this->pageViewAgedDays('/', 100);
        $this->pageViewAgedDays('/', 100);
        $this->artisan('page-views:prune', ['--days' => 90])->assertExitCode(0);

        $this->assertSame(3, (int) PageViewTotal::firstWhere('path', '/')->views);
        $this->assertSame(0, PageView::count());
    }

    public function test_days_below_thirty_is_floored_to_protect_the_sparkline_window(): void
    {
        $this->pageViewAgedDays('/', 35); // older than the 30-day floor → pruned
        $this->pageViewAgedDays('/', 20); // inside the sparkline window → kept

        $this->artisan('page-views:prune', ['--days' => 1])
            ->expectsOutputToContain('older than 30 days')
            ->assertExitCode(0);

        $this->assertSame(1, PageView::count());
        $this->assertSame(1, (int) PageViewTotal::firstWhere('path', '/')->views);
    }
}
