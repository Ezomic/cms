<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        return view('admin.skills.index', [
            'skills' => Skill::ordered()->get()->groupBy('category'),
        ]);
    }

    public function create()
    {
        return view('admin.skills.form', ['skill' => new Skill()]);
    }

    public function store(Request $request)
    {
        Skill::create($this->validated($request));

        return redirect()->route('admin.skills.index')->with('status', 'Skill added.');
    }

    public function edit(Skill $skill)
    {
        return view('admin.skills.form', compact('skill'));
    }

    public function update(Request $request, Skill $skill)
    {
        $skill->update($this->validated($request));

        return redirect()->route('admin.skills.index')->with('status', 'Skill updated.');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return back()->with('status', 'Skill deleted.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:skills,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            Skill::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->noContent();
    }

    public function trash()
    {
        return view('admin.skills.trash', [
            'skills' => Skill::onlyTrashed()->ordered()->get()->groupBy('category'),
        ]);
    }

    public function restore(int $id)
    {
        Skill::onlyTrashed()->findOrFail($id)->restore();

        return back()->with('status', 'Skill restored.');
    }

    public function forceDelete(int $id)
    {
        Skill::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('status', 'Skill permanently deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category'   => ['required', 'string', 'max:100'],
            'name'       => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
