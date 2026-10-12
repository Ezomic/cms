<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsPageViews;
use App\Models\Profile;
use App\Models\Project;
use Illuminate\View\View;

class LandingController extends Controller
{
    use RecordsPageViews;

    /**
     * The landing copy lives in lang files, so no updated_at tracks it.
     * Bump this date whenever the copy or layout changes (sitemap lastmod).
     */
    public const COPY_UPDATED_AT = '2026-10-13';

    public const PROOF_SLUGS = ['bluevi', 'data-exchange-portal', 'bioscape'];

    public function enschede(): View
    {
        $this->recordPageView();

        $bySlug = Project::published()->whereIn('slug', self::PROOF_SLUGS)->get()->keyBy('slug');

        return view('landing.enschede', [
            'profile' => Profile::current(),
            'proof' => collect(self::PROOF_SLUGS)->map(fn (string $slug): ?Project => $bySlug->get($slug))->filter()->values(),
        ]);
    }
}
