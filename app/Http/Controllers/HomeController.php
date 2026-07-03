<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
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

        return view('project', [
            'profile' => Profile::current(),
            'project' => $project,
        ]);
    }
}
