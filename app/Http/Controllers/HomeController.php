<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        PageView::create(['path' => '/']);

        // Caches the rendered HTML rather than the raw model data: caching
        // Eloquent instances directly proved unreliable to unserialize
        // reliably across requests, and rendering to a string sidesteps
        // that entirely while also skipping view compilation on cache hits.
        $html = Cache::rememberForever('home.page.html', function () {
            return view('home', [
                'profile'      => Profile::current(),
                'skills'       => Skill::ordered()->get()->groupBy('category'),
                'projects'     => Project::published()->ordered()->get(),
                'testimonial'  => Testimonial::where('featured', true)->latest()->first(),
            ])->render();
        });

        return response($html);
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
