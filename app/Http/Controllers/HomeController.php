<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);

        // Cached as plain arrays rather than raw Eloquent instances: caching
        // model objects directly proved unreliable to unserialize reliably
        // across requests in this environment (__PHP_Incomplete_Class),
        // and caching the full rendered HTML isn't safe either since it
        // would bake one visitor's CSRF token into every other visitor's
        // page. Arrays cache and restore cleanly; we cast them back to
        // stdClass so the view's property access keeps working unchanged.
        $data = Cache::rememberForever('home.page.data', function () {
            return [
                'profile' => Profile::current()->toArray(),
                'skills' => Skill::ordered()->get()->groupBy('category')
                    ->map(fn ($items) => $items->map(fn ($skill) => $skill->toArray())->all())
                    ->all(),
                'projects' => Project::published()->ordered()->get()->map(fn ($project) => [
                    ...$project->toArray(),
                    'tag_list' => $project->tagList(),
                    'image_url' => $project->imageUrl(),
                    'outcome' => $project->outcome,
                ])->all(),
                'testimonials' => Testimonial::where('featured', true)->latest()->get()->map->toArray()->all(),
            ];
        });

        return view('home', [
            'profile' => (object) $data['profile'],
            'skills' => collect($data['skills'])->map(fn ($items) => collect($items)->map(fn ($s) => (object) $s)),
            'projects' => collect($data['projects'])->map(fn ($p) => (object) $p),
            'testimonials' => collect($data['testimonials'])->map(fn ($t) => (object) $t),
        ]);
    }

    public function docs()
    {
        return view('docs', [
            'profile' => Profile::current(),
            'skills' => Skill::ordered()->get()->groupBy('category'),
            'projects' => Project::published()->ordered()->get(),
        ]);
    }

    public function work()
    {
        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);

        $projects = Project::published()->ordered()->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list' => $p->tagList(),
            'image_url' => $p->imageUrl(),
        ]);

        $tags = $projects->flatMap(fn ($p) => $p->tag_list)->unique()->sort()->values();

        return view('work', [
            'profile' => Profile::current(),
            'projects' => $projects,
            'tags' => $tags,
            'activeTag' => null,
        ]);
    }

    public function workTag(string $tag)
    {
        $allProjects = Project::published()->ordered()->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list' => $p->tagList(),
            'image_url' => $p->imageUrl(),
        ]);

        $tags = $allProjects->flatMap(fn ($p) => $p->tag_list)->unique()->sort()->values();

        abort_unless($tags->contains($tag), 404);

        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);

        $projects = $allProjects->filter(fn ($p) => in_array($tag, $p->tag_list))->values();

        return view('work', [
            'profile' => Profile::current(),
            'projects' => $projects,
            'tags' => $tags,
            'activeTag' => $tag,
        ]);
    }

    public function cv()
    {
        $profile = Profile::current();
        $skills = Skill::ordered()->get()->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($s) => (object) $s->toArray()));
        $projects = Project::published()->ordered()->take(4)->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list' => $p->tagList(),
        ]);

        $pdf = Pdf::loadView('cv', compact('profile', 'skills', 'projects'))
            ->setPaper('a4');

        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_text(
            497, 812, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 8, [0.66, 0.66, 0.66]
        );

        $filename = str($profile->name)->slug()->append('-cv.pdf')->toString();

        return $pdf->download($filename);
    }

    public function project(Project $project)
    {
        abort_unless($project->published, 404);

        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);

        return view('project', [
            'profile' => Profile::current(),
            'project' => $project,
        ]);
    }

    public function blog()
    {
        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);

        return view('blog', [
            'profile' => Profile::current(),
            'posts' => Post::published()->latest('published_at')->get(),
        ]);
    }

    public function post(Post $post)
    {
        abort_unless($post->published, 404);

        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);

        return view('blog-post', [
            'profile' => Profile::current(),
            'post' => $post,
        ]);
    }
}
