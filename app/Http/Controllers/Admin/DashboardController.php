<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'projectCount'     => Project::count(),
            'skillCount'       => Skill::count(),
            'testimonialCount' => Testimonial::count(),
        ]);
    }
}
