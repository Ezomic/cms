<?php

namespace App\Http\Controllers;

use App\Models\PageView;
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
        PageView::create(['path' => '/']);

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
                'skills'  => Skill::ordered()->get()->groupBy('category')
                    ->map(fn ($items) => $items->map(fn ($skill) => $skill->toArray())->all())
                    ->all(),
                'projects' => Project::published()->ordered()->get()->map(fn ($project) => [
                    ...$project->toArray(),
                    'tag_list'  => $project->tagList(),
                    'image_url' => $project->imageUrl(),
                    'outcome'   => $project->outcome,
                ])->all(),
                'testimonials' => Testimonial::latest()->get()->map->toArray()->all(),
            ];
        });

        return view('home', [
            'profile'      => (object) $data['profile'],
            'skills'       => collect($data['skills'])->map(fn ($items) => collect($items)->map(fn ($s) => (object) $s)),
            'projects'     => collect($data['projects'])->map(fn ($p) => (object) $p),
            'testimonials' => collect($data['testimonials'])->map(fn ($t) => (object) $t),
        ]);
    }

    public function docs()
    {
        return view('docs', [
            'profile'  => Profile::current(),
            'skills'   => Skill::ordered()->get()->groupBy('category'),
            'projects' => Project::published()->ordered()->get(),
        ]);
    }

    public function work()
    {
        PageView::create(['path' => '/work']);

        $projects = Project::published()->ordered()->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list'  => $p->tagList(),
            'image_url' => $p->imageUrl(),
        ]);

        $tags = $projects->flatMap(fn ($p) => $p->tag_list)->unique()->sort()->values();

        return view('work', [
            'profile'  => Profile::current(),
            'projects' => $projects,
            'tags'     => $tags,
        ]);
    }

    public function cv()
    {
        $profile = Profile::current();
        $skills  = Skill::ordered()->get()->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($s) => (object) $s->toArray()));
        $projects = Project::published()->ordered()->get()->map(fn ($p) => (object) [
            ...$p->toArray(),
            'tag_list' => $p->tagList(),
        ]);

        $pdf = Pdf::loadView('cv', compact('profile', 'skills', 'projects'))
            ->setPaper('a4');

        $filename = str($profile->name)->slug()->append('-cv.pdf')->toString();

        return $pdf->download($filename);
    }

    public function project(Project $project)
    {
        abort_unless($project->published, 404);

        PageView::create(['path' => '/work/'.$project->slug]);

        return view('project', [
            'profile' => Profile::current(),
            'project' => $project,
        ]);
    }
}
