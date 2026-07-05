@extends('layouts.admin')
@section('title', $post->exists ? 'Edit post' : 'New post')
@section('content')
  <h1 class="text-2xl font-semibold mb-8">{{ $post->exists ? 'Edit post' : 'New post' }}</h1>

  <form method="POST" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
        class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-xl">
    @csrf
    @if ($post->exists) @method('PUT') @endif

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Title</label>
      <input name="title" value="{{ old('title', $post->title) }}" required class="w-full border rounded px-3 py-2 text-sm @error('title') border-red-400 @else border-stone-300 @enderror">
      @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Slug</label>
      <input name="slug" value="{{ old('slug', $post->slug) }}" placeholder="Auto-generated from title if left blank" class="w-full border rounded px-3 py-2 text-sm @error('slug') border-red-400 @else border-stone-300 @enderror">
      @error('slug')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Excerpt</label>
      <textarea name="excerpt" rows="2" class="w-full border rounded px-3 py-2 text-sm @error('excerpt') border-red-400 @else border-stone-300 @enderror">{{ old('excerpt', $post->excerpt) }}</textarea>
      @error('excerpt')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Body (HTML)</label>
      <textarea name="body" rows="14" class="w-full border rounded px-3 py-2 text-sm font-mono @error('body') border-red-400 @else border-stone-300 @enderror">{{ old('body', $post->body) }}</textarea>
      @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta title</label>
        <input name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="w-full border rounded px-3 py-2 text-sm @error('meta_title') border-red-400 @else border-stone-300 @enderror">
        @error('meta_title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta description</label>
        <input name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="w-full border rounded px-3 py-2 text-sm @error('meta_description') border-red-400 @else border-stone-300 @enderror">
        @error('meta_description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="published" value="1" {{ old('published', $post->published) ? 'checked' : '' }}>
      Published (visible on the live blog)
    </label>

    <div class="flex gap-3 pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save</button>
      <a href="{{ route('admin.posts.index') }}" class="text-sm text-stone-500 px-4 py-2">Cancel</a>
    </div>
  </form>
@endsection
