@extends('layouts.admin')
@section('title', 'Blog')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Blog</h1>
    <div class="flex items-center gap-4">
      <a href="{{ route('admin.posts.trash') }}" class="text-sm text-stone-500 hover:text-orange-600">Trash</a>
      <a href="{{ route('admin.posts.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New post</a>
    </div>
  </div>

  <form method="GET" class="mb-4 flex items-center gap-3">
    <div class="relative w-full max-w-sm">
      <input type="text" name="search" value="{{ $search }}" placeholder="Search by title…"
             class="w-full border border-stone-300 rounded px-3 py-2 pr-8 text-sm">
      @if ($search)
        <a href="{{ route('admin.posts.index') }}" aria-label="Clear search" class="absolute right-2 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700">&times;</a>
      @endif
    </div>
    @if ($search)
      <span class="text-xs text-stone-400">{{ $posts->total() }} {{ Str::plural('result', $posts->total()) }}</span>
    @endif
  </form>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($posts as $post)
      <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-6 py-4">
        <div class="max-w-lg">
          <div class="text-sm font-medium">{{ $post->title }}</div>
          <div class="text-xs text-stone-500 mt-1">
            {{ $post->published ? 'Published' : 'Draft' }}
            @if ($post->published_at) · {{ $post->published_at->format('M j, Y') }} @endif
          </div>
        </div>
        <div class="flex gap-3 text-sm">
          @if ($post->published)
            <a href="{{ route('blog.show', $post->slug) }}" class="text-stone-600 hover:text-orange-600">View</a>
          @endif
          <a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-600 hover:text-orange-600">Edit</a>
          <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">
        {{ $search ? 'No posts match your search.' : 'No posts yet.' }}
      </div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $posts->links() }}
  </div>
@endsection
