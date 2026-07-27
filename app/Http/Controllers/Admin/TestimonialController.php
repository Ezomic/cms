<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesSoftDeleteActions;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TestimonialController extends Controller
{
    /** @use HandlesSoftDeleteActions<Testimonial> */
    use HandlesSoftDeleteActions;

    protected function softDeleteModel(): string
    {
        return Testimonial::class;
    }

    public function index(Request $request): InertiaResponse
    {
        $search = $request->string('search')->trim()->toString();

        $testimonials = Testimonial::latest()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('quote', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            }))
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Testimonial $t) => [
                'id' => $t->id,
                'quote' => $t->quote,
                'author_name' => $t->author_name,
                'author_role' => $t->author_role,
                'company_name' => $t->company_name,
                'featured' => $t->featured,
            ]);

        return Inertia::render('Testimonials/Index', [
            'testimonials' => $testimonials,
            'filters' => ['search' => $search],
            'trashCount' => Testimonial::onlyTrashed()->count(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Testimonials/Form', ['testimonial' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial): InertiaResponse
    {
        return Inertia::render('Testimonials/Form', [
            'testimonial' => [
                'id' => $testimonial->id,
                'quote' => $testimonial->quote,
                'quote_nl' => $testimonial->quote_nl,
                'author_name' => $testimonial->author_name,
                'author_role' => $testimonial->author_role,
                'company_name' => $testimonial->company_name,
                'featured' => $testimonial->featured,
            ],
        ]);
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

    public function trash(): InertiaResponse
    {
        return Inertia::render('Testimonials/Trash', [
            'testimonials' => Testimonial::onlyTrashed()->latest()->get()->map(fn (Testimonial $t) => [
                'id' => $t->id,
                'quote' => $t->quote,
                'author_name' => $t->author_name,
                'deleted_at' => $t->deleted_at?->diffForHumans(),
            ]),
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
