<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        PageView::create(['path' => '/']);

        return view('home', [
            'profile'      => Profile::current(),
            'skills'       => Skill::ordered()->get()->groupBy('category'),
            'projects'     => Project::published()->ordered()->get(),
            'testimonial'  => Testimonial::where('featured', true)->latest()->first(),
        ]);
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
