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

        $projectsPerTag = $projects->flatMap(fn (Project $project) => array_unique($project->tagList()))->countBy();
        $indexedTags = $tags->filter(fn (string $tag): bool => $projectsPerTag[$tag] >= Project::MIN_PROJECTS_FOR_INDEXED_TAG);

        $entries = collect([
            ['route' => 'home', 'params' => [], 'lastmod' => $lastModified->home()],
            ['route' => 'landing.enschede', 'params' => [], 'lastmod' => $lastModified->landing()],
            ['route' => 'work.index', 'params' => [], 'lastmod' => $lastModified->work()],
            ['route' => 'docs', 'params' => [], 'lastmod' => $lastModified->docs()],
        ])
            ->concat($indexedTags->map(fn (string $tag) => ['route' => 'work.tag', 'params' => ['slug' => $tagSlugs->slug($tag)], 'lastmod' => $lastModified->tag($projects, $tag)]))
            ->concat($projects->map(fn (Project $project) => ['route' => 'project.show', 'params' => ['project' => $project->slug], 'lastmod' => $project->updated_at]));

        return response()
            ->view('sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml');
    }
}
