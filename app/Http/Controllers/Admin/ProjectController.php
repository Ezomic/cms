<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        Project::create($this->validated($request, new Project()));

        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request, $project));

        return redirect()->route('admin.projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with('status', 'Project deleted.');
    }

    private function validated(Request $request, Project $project): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($project->id)],
            'client_name' => ['nullable', 'string', 'max:255'],
            'year'        => ['nullable', 'string', 'max:4'],
            'description' => ['nullable', 'string'],
            'body'        => ['nullable', 'string'],
            'tags'        => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer'],
        ]);
    }
}
