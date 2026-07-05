<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $posts = Post::latest('published_at')
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->paginate(10)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.posts.form', ['post' => new Post]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, new Post);
        $data['published'] = $request->boolean('published');

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('status', 'Post created.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request, $post);
        $data['published'] = $request->boolean('published');

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }

    public function trash()
    {
        return view('admin.posts.trash', [
            'posts' => Post::onlyTrashed()->latest()->get(),
        ]);
    }

    public function restore(int $id)
    {
        Post::onlyTrashed()->findOrFail($id)->restore();

        return back()->with('status', 'Post restored.');
    }

    public function forceDelete(int $id)
    {
        Post::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('status', 'Post permanently deleted.');
    }

    private function validated(Request $request, Post $post): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
