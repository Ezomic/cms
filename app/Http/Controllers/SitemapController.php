<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ContentLastModified;
use App\Support\TagSlugs;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(ContentLastModified $lastModified): Response
    {
        $projects = Project::published()->ordered()->get();
        $tags = $projects->flatMap(fn (Project $project) => $project->tagList())->unique()->sort()->values();

        $tagSlugs = TagSlugs::from($tags);

        $entries = collect([
            ['route' => 'home', 'params' => [], 'lastmod' => $lastModified->home()],
            ['route' => 'landing.enschede', 'params' => [], 'lastmod' => $lastModified->landing()],
            ['route' => 'work.index', 'params' => [], 'lastmod' => $lastModified->work()],
            ['route' => 'docs', 'params' => [], 'lastmod' => $lastModified->docs()],
        ])
            ->concat($tags->map(fn (string $tag) => ['route' => 'work.tag', 'params' => ['slug' => $tagSlugs->slug($tag)], 'lastmod' => $lastModified->tag($projects, $tag)]))
            ->concat($projects->map(fn (Project $project) => ['route' => 'project.show', 'params' => ['project' => $project->slug], 'lastmod' => $project->updated_at]));

        return response()
            ->view('sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml');
    }
}
