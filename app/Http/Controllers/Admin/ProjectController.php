<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $projects = Project::ordered()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%");
            }))
            ->paginate(10)
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'search'   => $search,
        ]);
    }

    public function create()
    {
        return view('admin.projects.form', ['project' => new Project()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, new Project());
        $data['published'] = $request->boolean('published');

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeOptimizedImage($request->file('image'));
        }

        Project::create($data);

        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validated($request, $project);
        $data['published'] = $request->boolean('published');

        if ($request->hasFile('image')) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $data['image'] = $this->storeOptimizedImage($request->file('image'));
        }

        $project->update($data);

        return redirect()->route('admin.projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return back()->with('status', 'Project deleted.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:projects,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            Project::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->noContent();
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

    private function validated(Request $request, Project $project): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'max:4096'],
            'slug'        => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($project->id)],
            'client_name' => ['nullable', 'string', 'max:255'],
            'year'        => ['nullable', 'string', 'max:4'],
            'description' => ['nullable', 'string'],
            'outcome'     => ['nullable', 'string', 'max:255'],
            'body'        => ['nullable', 'string'],
            'tags'        => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer'],
        ]);
    }

    /**
     * Store the uploaded image, downscaled to a max width so a phone photo
     * doesn't ship multi-megabyte originals to every site visitor.
     */
    private function storeOptimizedImage(UploadedFile $file): string
    {
        $path = $file->store('projects', 'public');
        $fullPath = Storage::disk('public')->path($path);

        ImageManager::gd()
            ->read($fullPath)
            ->scaleDown(width: 1600)
            ->save($fullPath, quality: 82);

        return $path;
    }
}
