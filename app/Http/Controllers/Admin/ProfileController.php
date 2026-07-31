<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProfileController extends Controller
{
    public function edit(): InertiaResponse
    {
        $profile = Profile::current();

        return Inertia::render('Profile/Edit', [
            'profile' => $profile->only([
                'name', 'city', 'tagline', 'hero_headline', 'hero_subtext', 'available',
                'email', 'linkedin_url', 'github_url', 'rate', 'availability_from', 'kvk_number',
                'meta_title', 'meta_description', 'docs_intro',
                'tagline_nl', 'hero_headline_nl', 'hero_subtext_nl', 'docs_intro_nl',
                'meta_title_nl', 'meta_description_nl',
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'tagline' => ['required', 'string', 'max:255'],
            'hero_headline' => ['required', 'string', 'max:255'],
            'hero_subtext' => ['nullable', 'string'],
            'available' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'rate' => ['nullable', 'string', 'max:100'],
            'availability_from' => ['nullable', 'string', 'max:100'],
            'kvk_number' => ['nullable', 'string', 'max:50'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'docs_intro' => ['nullable', 'string'],
            'tagline_nl' => ['nullable', 'string', 'max:255'],
            'hero_headline_nl' => ['nullable', 'string', 'max:255'],
            'hero_subtext_nl' => ['nullable', 'string'],
            'docs_intro_nl' => ['nullable', 'string'],
            'meta_title_nl' => ['nullable', 'string', 'max:255'],
            'meta_description_nl' => ['nullable', 'string', 'max:255'],
        ]);

        $attributes = [];

        foreach (is_array($data) ? $data : [] as $key => $value) {
            $attributes[(string) $key] = $value;
        }

        $attributes['available'] = $request->boolean('available');

        Profile::current()->update($attributes);

        return back()->with('status', 'Profile updated.');
    }
}
