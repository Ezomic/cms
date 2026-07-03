<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return view('admin.projects.index', [
            'projects' => Project::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.projects.form', ['project' => new Project()]);
    }

    public function store(Request $request)
    {
        Project::create($this->validated($request));

        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));

        return redirect()->route('admin.projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with('status', 'Project deleted.');
    }

    public function trash()
    {
        return view('admin.projects.trash', [
            'projects' => Project::onlyTrashed()->ordered()->get(),
        ]);
    }

    public function restore(int $id)
    {
        Project::onlyTrashed()->findOrFail($id)->restore();

        return back()->with('status', 'Project restored.');
    }

    public function forceDelete(int $id)
    {
        Project::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('status', 'Project permanently deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'year'        => ['nullable', 'string', 'max:4'],
            'description' => ['nullable', 'string'],
            'tags'        => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer'],
        ]);
    }
}
