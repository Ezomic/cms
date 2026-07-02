<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::latest()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial()]);
    }

    public function store(Request $request)
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $testimonial->update($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('status', 'Testimonial deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'quote'       => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'featured'    => ['nullable', 'boolean'],
        ]);

        $data['featured'] = $request->boolean('featured');

        return $data;
    }
}
