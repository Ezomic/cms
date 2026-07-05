@extends('layouts.admin')
@section('title', 'Blog · Trash')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Blog · Trash</h1>
    <a href="{{ route('admin.posts.index') }}" class="text-sm text-stone-500 hover:text-orange-600">← Back to blog</a>
  </div>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($posts as $post)
      <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-6 py-4">
        <div class="max-w-lg">
          <div class="text-sm font-medium">{{ $post->title }}</div>
        </div>
        <div class="flex gap-3 text-sm">
          <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}">
            @csrf
            <button class="text-stone-600 hover:text-orange-600">Restore</button>
          </form>
          <form method="POST" action="{{ route('admin.posts.forceDelete', $post->id) }}" onsubmit="return confirm('Permanently delete this post? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete permanently</button>
          </form>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">Trash is empty.</div>
    @endforelse
  </div>
@endsection
