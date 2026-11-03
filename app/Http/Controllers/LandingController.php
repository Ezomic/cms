<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsPageViews;
use App\Models\Profile;
use App\Models\Project;
use App\Support\LandingPages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LandingController extends Controller
{
    use RecordsPageViews;

    /**
     * The landing copy lives in lang files, so no updated_at tracks it.
     * Bump this date whenever the copy or layout changes (sitemap lastmod).
     */
    public const COPY_UPDATED_AT = '2026-11-02';

    public const REMOTE_COPY_UPDATED_AT = '2026-11-02';

    public const PROOF_SLUGS = ['bluevi', 'data-exchange-portal', 'bioscape'];

    public function enschede(): View
    {
        return $this->landing('landing.enschede');
    }

    public function remote(): View
    {
        return $this->landing('landing.remote');
    }

    public function page(Request $request): View
    {
        $key = Str::after((string) $request->route()?->getName(), 'landing.');
        abort_unless(in_array($key, LandingPages::keys(), true), 404);

        $this->recordPageView();

        $slugs = LandingPages::projectSlugs($key);
        $published = $slugs === [] ? [] : Project::published()->whereIn('slug', $slugs)->pluck('slug')->all();

        $proof = collect(LandingPages::all()[$key]['proof'])->map(function (array $entry) use ($published): array {
            $project = $entry['project'] ?? null;

            return [
                'key' => $entry['key'],
                'url' => $entry['url'] ?? null,
                'project' => in_array($project, $published, true) ? $project : null,
            ];
        });

        return view('landing.page', [
            'profile' => Profile::current(),
            'page' => $key,
            'lang' => 'pages/'.$key,
            'proof' => $proof,
        ]);
    }

    /**
     * @param  view-string  $view
     */
    private function landing(string $view): View
    {
        $this->recordPageView();

        $bySlug = Project::published()->whereIn('slug', self::PROOF_SLUGS)->get()->keyBy('slug');

        return view($view, [
            'profile' => Profile::current(),
            'proof' => collect(self::PROOF_SLUGS)->map(fn (string $slug): ?Project => $bySlug->get($slug))->filter()->values(),
        ]);
    }
}
