<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $testimonials = Testimonial::latest()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('quote', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            }))
            ->paginate(10)
            ->withQueryString();

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial]);
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

    public function trash()
    {
        return view('admin.testimonials.trash', [
            'testimonials' => Testimonial::onlyTrashed()->latest()->get(),
        ]);
    }

    public function restore(int $id)
    {
        Testimonial::onlyTrashed()->findOrFail($id)->restore();

        return back()->with('status', 'Testimonial restored.');
    }

    public function forceDelete(int $id)
    {
        Testimonial::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('status', 'Testimonial permanently deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'quote' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'quote_nl' => ['nullable', 'string'],
        ]);

        $data['featured'] = $request->boolean('featured');

        return $data;
    }
}
