<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesSoftDeleteActions;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PostController extends Controller
{
    /** @use HandlesSoftDeleteActions<Post> */
    use HandlesSoftDeleteActions;

    protected function softDeleteModel(): string
    {
        return Post::class;
    }

    public function index(Request $request): InertiaResponse
    {
        $search = $request->string('search')->trim()->toString();

        $posts = Post::latest('published_at')
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'published' => $post->published,
                'published_at' => $post->published_at?->translatedFormat('M j, Y'),
            ]);

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'filters' => ['search' => $search],
            'trashCount' => Post::onlyTrashed()->count(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Posts/Form', ['post' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, new Post);
        $data['published'] = $request->boolean('published');

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('status', 'Post created.');
    }

    public function edit(Post $post): InertiaResponse
    {
        return Inertia::render('Posts/Form', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'published' => $post->published,
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
                'title_nl' => $post->title_nl,
                'excerpt_nl' => $post->excerpt_nl,
                'body_nl' => $post->body_nl,
                'meta_title_nl' => $post->meta_title_nl,
                'meta_description_nl' => $post->meta_description_nl,
            ],
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request, $post);
        $data['published'] = $request->boolean('published');

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }

    public function trash(): InertiaResponse
    {
        return Inertia::render('Posts/Trash', [
            'posts' => Post::onlyTrashed()->latest()->get()->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'deleted_at' => $post->deleted_at?->diffForHumans(),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Post $post): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'title_nl' => ['nullable', 'string', 'max:255'],
            'excerpt_nl' => ['nullable', 'string', 'max:500'],
            'body_nl' => ['nullable', 'string'],
            'meta_title_nl' => ['nullable', 'string', 'max:255'],
            'meta_description_nl' => ['nullable', 'string', 'max:255'],
        ]);

        $attributes = [];

        foreach (is_array($validated) ? $validated : [] as $key => $value) {
            $attributes[(string) $key] = $value;
        }

        return $attributes;
    }
}
