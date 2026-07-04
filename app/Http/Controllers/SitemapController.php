<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $projects = Project::published()->ordered()->get();
        $tags = $projects->flatMap(fn (Project $project) => $project->tagList())->unique()->sort()->values();
        $profileUpdatedAt = Profile::current()->updated_at;

        $entries = collect([
            ['route' => 'home', 'params' => [], 'lastmod' => $profileUpdatedAt],
            ['route' => 'work.index', 'params' => [], 'lastmod' => $projects->max('updated_at')],
            ['route' => 'docs', 'params' => [], 'lastmod' => $profileUpdatedAt],
        ])
            ->concat($tags->map(fn (string $tag) => ['route' => 'work.tag', 'params' => ['tag' => $tag], 'lastmod' => null]))
            ->concat($projects->map(fn (Project $project) => ['route' => 'project.show', 'params' => ['project' => $project->slug], 'lastmod' => $project->updated_at]));

        return response()
            ->view('sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml');
    }
}
