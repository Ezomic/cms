<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesSoftDeleteActions;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    /** @use HandlesSoftDeleteActions<Testimonial> */
    use HandlesSoftDeleteActions;

    protected function softDeleteModel(): string
    {
        return Testimonial::class;
    }

    public function index(Request $request): View
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

    public function create(): View
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('status', 'Testimonial deleted.');
    }

    public function trash(): View
    {
        return view('admin.testimonials.trash', [
            'testimonials' => Testimonial::onlyTrashed()->latest()->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
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
