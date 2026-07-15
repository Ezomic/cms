<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesReordering;
use App\Http\Controllers\Concerns\HandlesSoftDeleteActions;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $skills = Skill::ordered()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->get()
            ->groupBy('category');

        return view('admin.skills.index', [
            'skills' => $skills,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.skills.form', ['skill' => new Skill]);
    }

    public function store(Request $request): RedirectResponse
    {
        Skill::create($this->validated($request));

        return redirect()->route('admin.skills.index')->with('status', 'Skill added.');
    }

    public function edit(Skill $skill): View
    {
        return view('admin.skills.form', compact('skill'));
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

    public function trash(): View
    {
        return view('admin.skills.trash', [
            'skills' => Skill::onlyTrashed()->ordered()->get()->groupBy('category'),
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
