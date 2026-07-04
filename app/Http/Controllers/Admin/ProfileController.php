<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.edit', [
            'profile' => Profile::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'city'              => ['required', 'string', 'max:255'],
            'tagline'           => ['required', 'string', 'max:255'],
            'hero_headline'     => ['required', 'string', 'max:255'],
            'hero_subtext'      => ['nullable', 'string'],
            'available'         => ['nullable', 'boolean'],
            'email'             => ['nullable', 'email', 'max:255'],
            'linkedin_url'      => ['nullable', 'url', 'max:255'],
            'github_url'        => ['nullable', 'url', 'max:255'],
            'rate'              => ['nullable', 'string', 'max:100'],
            'availability_from' => ['nullable', 'string', 'max:100'],
            'kvk_number'        => ['nullable', 'string', 'max:50'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:255'],
            'docs_intro'        => ['nullable', 'string'],
        ]);

        $data['available'] = $request->boolean('available');

        Profile::current()->update($data);

        return back()->with('status', 'Profile updated.');
    }
}
