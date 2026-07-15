<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesReordering;
use App\Http\Controllers\Concerns\HandlesSoftDeleteActions;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\ImageManager;

class ProjectController extends Controller
{
    use HandlesReordering;

    /** @use HandlesSoftDeleteActions<Project> */
    use HandlesSoftDeleteActions;

    protected function softDeleteModel(): string
    {
        return Project::class;
    }

    protected function reorderModel(): string
    {
        return Project::class;
    }

    protected function beforeForceDelete(Model $model): void
    {
        if (! $model instanceof Project) {
            return;
        }

        foreach ($model->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
    }

    public function index(Request $request): View
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
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateGallery($request);

        $data = $this->validated($request, new Project);
        $data['published'] = $request->boolean('published');

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeOptimizedImage($request->file('image'));
        }

        $project = Project::create($data);

        $this->storeGalleryUploads($request, $project);

        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->validateGallery($request);

        $data = $this->validated($request, $project);
        $data['published'] = $request->boolean('published');

        if ($request->hasFile('image')) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $data['image'] = $this->storeOptimizedImage($request->file('image'));
        }

        $project->update($data);

        $this->removeGalleryImages($request, $project);
        $this->storeGalleryUploads($request, $project);

        return redirect()->route('admin.projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return back()->with('status', 'Project deleted.');
    }

    public function trash(): View
    {
        return view('admin.projects.trash', [
            'projects' => Project::onlyTrashed()->ordered()->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Project $project): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($project->id)],
            'github_url' => ['nullable', 'url', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:4'],
            'description' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'tags' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'description_nl' => ['nullable', 'string'],
            'outcome_nl' => ['nullable', 'string', 'max:255'],
            'body_nl' => ['nullable', 'string'],
            'image_alt_nl' => ['nullable', 'string', 'max:255'],
            'meta_title_nl' => ['nullable', 'string', 'max:255'],
            'meta_description_nl' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * Store the uploaded image, downscaled to a max width so a phone photo
     * doesn't ship multi-megabyte originals to every site visitor.
     */
    private function storeOptimizedImage(UploadedFile $file): string
    {
        $path = $file->store('projects', 'public');

        if ($path === false) {
            throw new \RuntimeException('Failed to store uploaded project image.');
        }

        $fullPath = Storage::disk('public')->path($path);

        ImageManager::gd()
            ->read($fullPath)
            ->scaleDown(width: 1600)
            ->save($fullPath, quality: 82);

        return $path;
    }

    private function validateGallery(Request $request): void
    {
        $request->validate([
            'gallery' => ['nullable', 'array', 'max:8'],
            'gallery.*' => ['image', 'max:4096'],
        ]);
    }

    private function storeGalleryUploads(Request $request, Project $project): void
    {
        if (! $request->hasFile('gallery')) {
            return;
        }

        $nextSortOrder = $project->images()->max('sort_order') + 1;

        foreach ($request->file('gallery') as $file) {
            $project->images()->create([
                'path' => $this->storeOptimizedImage($file),
                'sort_order' => $nextSortOrder++,
            ]);
        }
    }

    private function removeGalleryImages(Request $request, Project $project): void
    {
        $data = $request->validate([
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('project_images', 'id')],
        ]);

        if (empty($data['remove_images'])) {
            return;
        }

        $images = $project->images()->whereIn('id', $data['remove_images'])->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }
}
