<?php

namespace Tests\Feature\Admin;

use App\Models\PageView;
use App\Models\PageViewTotal;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectViewCountTest extends TestCase
{
    use RefreshDatabase;

    private function project(string $name = 'Acme Site'): Project
    {
        return Project::create(['name' => $name, 'published' => true, 'sort_order' => 0]);
    }

    private function indexAssert(callable $assertions): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/projects')
            ->assertInertia(fn (Assert $page) => $assertions($page));
    }

    public function test_it_counts_live_page_views(): void
    {
        $this->project();
        PageView::create(['path' => '/work/acme-site']);
        PageView::create(['path' => '/work/acme-site']);

        $this->indexAssert(fn (Assert $p) => $p->where('projects.data.0.views', 2)->etc());
    }

    /**
     * A project is reachable in both locales. Counting only the unprefixed
     * path silently halves every number.
     */
    public function test_it_sums_both_locale_paths(): void
    {
        $this->project();
        PageView::create(['path' => '/work/acme-site']);
        PageView::create(['path' => '/nl/work/acme-site']);
        PageView::create(['path' => '/nl/work/acme-site']);

        $this->indexAssert(fn (Assert $p) => $p->where('projects.data.0.views', 3)->etc());
    }

    /**
     * page-views:prune moves old rows into page_view_totals, so counting only
     * page_views makes figures shrink over time.
     */
    public function test_it_includes_pruned_history_from_page_view_totals(): void
    {
        $this->project();
        PageView::create(['path' => '/work/acme-site']);
        PageViewTotal::create(['path' => '/work/acme-site', 'views' => 40]);
        PageViewTotal::create(['path' => '/nl/work/acme-site', 'views' => 9]);

        $this->indexAssert(fn (Assert $p) => $p->where('projects.data.0.views', 50)->etc());
    }

    public function test_a_project_with_no_traffic_reports_zero(): void
    {
        $this->project();

        $this->indexAssert(fn (Assert $p) => $p->where('projects.data.0.views', 0)->etc());
    }

    public function test_it_does_not_count_other_pages_or_other_projects(): void
    {
        $this->project();
        PageView::create(['path' => '/work/acme-site']);
        PageView::create(['path' => '/work']);
        PageView::create(['path' => '/']);
        PageView::create(['path' => '/work/some-other-project']);
        PageView::create(['path' => '/work/acme-site/preview']);

        $this->indexAssert(fn (Assert $p) => $p->where('projects.data.0.views', 1)->etc());
    }

    public function test_counts_are_resolved_without_a_query_per_row(): void
    {
        foreach (range(1, 8) as $i) {
            Project::create(['name' => "Project {$i}", 'published' => true, 'sort_order' => $i]);
            PageView::create(['path' => "/work/project-{$i}"]);
        }

        $user = User::factory()->create();

        DB::enableQueryLog();
        $this->actingAs($user)->get('/admin/projects')->assertOk();
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $pageViewQueries = $queries->filter(fn (string $q) => str_contains($q, 'page_views'))->count();
        $totalsQueries = $queries->filter(fn (string $q) => str_contains($q, 'page_view_totals'))->count();

        $this->assertSame(1, $pageViewQueries, 'page_views should be aggregated in a single grouped query');
        $this->assertSame(1, $totalsQueries, 'page_view_totals should be read once');
    }
}
