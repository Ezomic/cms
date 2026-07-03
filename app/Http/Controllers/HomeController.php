<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $data = Cache::rememberForever('home.page.data', function () {
            return [
                'profile'      => Profile::current(),
                'skills'       => Skill::ordered()->get()->groupBy('category'),
                'projects'     => Project::ordered()->get(),
                'testimonial'  => Testimonial::where('featured', true)->latest()->first(),
            ];
        });

        return view('home', $data);
    }
}
