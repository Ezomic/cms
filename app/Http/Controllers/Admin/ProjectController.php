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
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
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

        if ($model->image) {
            Storage::disk('public')->delete($model->image);
        }

        foreach ($model->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
    }

    public function index(Request $request): InertiaResponse
    {
        $search = $request->string('search')->trim()->toString();

        $projects = Project::ordered()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%");
            }))
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'client_name' => $project->client_name,
                'year' => $project->year,
                'published' => $project->published,
                'tag_list' => $project->tagList(),
                'image_url' => $project->imageUrl(),
            ]);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => ['search' => $search],
            'trashCount' => Project::onlyTrashed()->count(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Projects/Form', ['project' => null]);
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

    public function edit(Project $project): InertiaResponse
    {
        $project->load('images');

        return Inertia::render('Projects/Form', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'client_name' => $project->client_name,
                'year' => $project->year,
                'github_url' => $project->github_url,
                'description' => $project->description,
                'outcome' => $project->outcome,
                'body' => $project->body,
                'tags' => $project->tags,
                'published' => $project->published,
                'meta_title' => $project->meta_title,
                'meta_description' => $project->meta_description,
                'image_url' => $project->imageUrl(),
                'image_alt' => $project->image_alt,
                'description_nl' => $project->description_nl,
                'outcome_nl' => $project->outcome_nl,
                'body_nl' => $project->body_nl,
                'image_alt_nl' => $project->image_alt_nl,
                'meta_title_nl' => $project->meta_title_nl,
                'meta_description_nl' => $project->meta_description_nl,
                'images' => $project->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->imageUrl(),
                ]),
                'preview_url' => $this->previewUrl($project),
            ],
        ]);
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
        // The image file stays on disk: this only soft-deletes, and a restored
        // project whose image column points at a deleted file renders broken.
        // Cleanup happens in beforeForceDelete(), alongside the gallery files.
        $project->delete();

        return back()->with('status', 'Project deleted.');
    }

    public function trash(): InertiaResponse
    {
        return Inertia::render('Projects/Trash', [
            'projects' => Project::onlyTrashed()->ordered()->get()->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'deleted_at' => $project->deleted_at?->diffForHumans(),
            ]),
        ]);
    }

    /**
     * A signed link to the public case-study page, so a draft can be reviewed
     * as visitors would see it without publishing it first.
     */
    private function previewUrl(Project $project): string
    {
        return URL::temporarySignedRoute('project.preview', now()->addDay(), ['project' => $project->slug]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Project $project): array
    {
        $validated = $request->validate([
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

        $attributes = [];

        foreach (is_array($validated) ? $validated : [] as $key => $value) {
            $attributes[(string) $key] = $value;
        }

        return $attributes;
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

        $maxSortOrder = $project->images()->max('sort_order');
        $nextSortOrder = (is_numeric($maxSortOrder) ? (int) $maxSortOrder : 0) + 1;

        $uploads = $request->file('gallery');

        foreach (is_array($uploads) ? $uploads : [$uploads] as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $project->images()->create([
                'path' => $this->storeOptimizedImage($file),
                'sort_order' => $nextSortOrder++,
            ]);
        }
    }

    private function removeGalleryImages(Request $request, Project $project): void
    {
        $request->validate([
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('project_images', 'id')],
        ]);

        $removeImages = $request->collect('remove_images')->values()->all();

        if ($removeImages === []) {
            return;
        }

        $images = $project->images()->whereIn('id', $removeImages)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }
}
