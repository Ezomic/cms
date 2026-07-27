<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesReordering;
use App\Http\Controllers\Concerns\HandlesSoftDeleteActions;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SkillController extends Controller
{
    use HandlesReordering;

    /** @use HandlesSoftDeleteActions<Skill> */
    use HandlesSoftDeleteActions;

    protected function softDeleteModel(): string
    {
        return Skill::class;
    }

    protected function reorderModel(): string
    {
        return Skill::class;
    }

    public function index(Request $request): InertiaResponse
    {
        $search = $request->string('search')->trim()->toString();

        $skills = Skill::ordered()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->get()
            ->map(fn (Skill $skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
            ]);

        return Inertia::render('Skills/Index', [
            'skills' => $skills,
            'filters' => ['search' => $search],
            'trashCount' => Skill::onlyTrashed()->count(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Skills/Form', ['skill' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Skill::create($this->validated($request));

        return redirect()->route('admin.skills.index')->with('status', 'Skill added.');
    }

    public function edit(Skill $skill): InertiaResponse
    {
        return Inertia::render('Skills/Form', [
            'skill' => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
            ],
        ]);
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $skill->update($this->validated($request));

        return redirect()->route('admin.skills.index')->with('status', 'Skill updated.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        return back()->with('status', 'Skill deleted.');
    }

    public function trash(): InertiaResponse
    {
        return Inertia::render('Skills/Trash', [
            'skills' => Skill::onlyTrashed()->ordered()->get()->map(fn (Skill $skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
                'deleted_at' => $skill->deleted_at?->diffForHumans(),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
