<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentCompleteness;
use App\Services\ContentGap;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContentGapController extends Controller
{
    public function index(ContentCompleteness $completeness): InertiaResponse
    {
        return Inertia::render('ContentGaps/Index', [
            'rows' => $completeness->report()->map(fn (ContentGap $gap): array => $gap->toArray())->values(),
        ]);
    }
}
