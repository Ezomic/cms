<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageViewRecordingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every public page records a view under its own path. Kept as one test
     * over the whole route list so a newly added public page that forgets to
     * record is caught here rather than silently missing from the dashboard.
     *
     * @return array<string, array{0: string}>
     */
    public static function publicPaths(): array
    {
        return [
            'home' => ['/'],
            'home nl' => ['/nl'],
            'docs' => ['/docs'],
            'docs nl' => ['/nl/docs'],
            'work' => ['/work'],
            'work nl' => ['/nl/work'],
        ];
    }

    #[DataProvider('publicPaths')]
    public function test_public_pages_record_a_page_view(string $path): void
    {
        $this->get($path)->assertOk();

        $this->assertSame(1, PageView::where('path', $path)->count());
    }

    public function test_project_and_tag_pages_record_a_page_view(): void
    {
        Project::create(['name' => 'Acme Site', 'tags' => 'laravel', 'published' => true, 'sort_order' => 0]);

        $this->get('/work/tag/laravel')->assertOk();
        $this->get('/work/acme-site')->assertOk();

        $this->assertSame(1, PageView::where('path', '/work/tag/laravel')->count());
        $this->assertSame(1, PageView::where('path', '/work/acme-site')->count());
    }
}
