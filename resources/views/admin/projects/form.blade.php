@extends('layouts.admin')
@section('title', $project->exists ? 'Edit project' : 'New project')
@section('content')
  <h1 class="text-2xl font-semibold mb-8">{{ $project->exists ? 'Edit project' : 'New project' }}</h1>

  <form method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}"
        enctype="multipart/form-data"
        class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-xl">
    @csrf
    @if ($project->exists) @method('PUT') @endif

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Project name</label>
      <input name="name" value="{{ old('name', $project->name) }}" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Client name</label>
        <input name="client_name" value="{{ old('client_name', $project->client_name) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Year</label>
        <input name="year" value="{{ old('year', $project->year) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Image</label>
      @if ($project->image)
        <img src="{{ $project->imageUrl() }}" alt="" class="w-40 h-28 object-cover rounded border border-stone-200 mb-2">
      @endif
      <input type="file" name="image" accept="image/*" class="w-full border border-stone-300 rounded px-3 py-2 text-sm bg-white">
      <p class="text-xs text-stone-400 mt-1">{{ $project->image ? 'Uploading a new image replaces the current one.' : 'JPG, PNG, or WebP, up to 4MB.' }}</p>
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Slug (URL)</label>
      <input name="slug" value="{{ old('slug', $project->slug) }}" placeholder="auto-generated from name if left blank" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      @if ($project->exists && $project->slug)
        <p class="text-xs text-stone-400 mt-1">{{ url('/work/'.$project->slug) }}</p>
      @endif
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Description (one line result)</label>
      <textarea name="description" rows="3" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">{{ old('description', $project->description) }}</textarea>
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Case study body (optional, shown on the project's own page)</label>
      <textarea name="body" rows="8" class="w-full border border-stone-300 rounded px-3 py-2 text-sm font-mono">{{ old('body', $project->body) }}</textarea>
      <p class="text-xs text-stone-400 mt-1">Plain text or basic HTML. Leave blank to skip the dedicated project page.</p>
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Tags (comma-separated)</label>
      <input name="tags" value="{{ old('tags', $project->tags) }}" placeholder="Next.js, Stripe" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Sort order</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $project->sort_order ?? 0) }}" class="w-32 border border-stone-300 rounded px-3 py-2 text-sm">
    </div>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="published" value="1" {{ old('published', $project->exists ? $project->published : true) ? 'checked' : '' }}>
      Published (visible on the live site)
    </label>

    <div class="flex gap-3 pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save</button>
      <a href="{{ route('admin.projects.index') }}" class="text-sm text-stone-500 px-4 py-2">Cancel</a>
    </div>
  </form>
@endsection
