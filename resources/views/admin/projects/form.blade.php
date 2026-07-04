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
      <input name="name" value="{{ old('name', $project->name) }}" required class="w-full border rounded px-3 py-2 text-sm @error('name') border-red-400 @else border-stone-300 @enderror">
      @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Client name</label>
        <input name="client_name" value="{{ old('client_name', $project->client_name) }}" class="w-full border rounded px-3 py-2 text-sm @error('client_name') border-red-400 @else border-stone-300 @enderror">
        @error('client_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Year</label>
        <input name="year" value="{{ old('year', $project->year) }}" class="w-full border rounded px-3 py-2 text-sm @error('year') border-red-400 @else border-stone-300 @enderror">
        @error('year')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Image</label>
      @if ($project->image)
        <img src="{{ $project->imageUrl() }}" alt="" class="w-40 h-28 object-cover rounded border border-stone-200 mb-2">
      @endif
      <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2 text-sm bg-white @error('image') border-red-400 @else border-stone-300 @enderror">
      @error('image')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        <p class="text-xs text-stone-400 mt-1">{{ $project->image ? 'Uploading a new image replaces the current one.' : 'JPG, PNG, or WebP, up to 4MB.' }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Image alt text</label>
      <input name="image_alt" value="{{ old('image_alt', $project->image_alt) }}" placeholder="Describe what the image shows, e.g. &quot;Dashboard screenshot showing the invoice list&quot;" class="w-full border rounded px-3 py-2 text-sm @error('image_alt') border-red-400 @else border-stone-300 @enderror">
      @error('image_alt')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        <p class="text-xs text-stone-400 mt-1">Read by screen readers. Falls back to the project name if left blank.</p>
      @enderror
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Slug (URL)</label>
      <input name="slug" value="{{ old('slug', $project->slug) }}" placeholder="auto-generated from name if left blank" class="w-full border rounded px-3 py-2 text-sm @error('slug') border-red-400 @else border-stone-300 @enderror">
      @error('slug')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        @if ($project->exists && $project->slug)
          <p class="text-xs text-stone-400 mt-1">{{ url('/work/'.$project->slug) }}</p>
        @endif
      @enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Description (one line result)</label>
      <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 text-sm @error('description') border-red-400 @else border-stone-300 @enderror">{{ old('description', $project->description) }}</textarea>
      @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Outcome (measurable result, shown as a callout)</label>
      <input name="outcome" value="{{ old('outcome', $project->outcome) }}" placeholder="e.g. Reduced load time by 60%, launched 2 weeks early" class="w-full border rounded px-3 py-2 text-sm @error('outcome') border-red-400 @else border-stone-300 @enderror">
      @error('outcome')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        <p class="text-xs text-stone-400 mt-1">One line. Shown on the project page and work archive. Leave blank to hide.</p>
      @enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Case study body (optional, shown on the project's own page)</label>
      <textarea name="body" rows="8" class="w-full border rounded px-3 py-2 text-sm font-mono @error('body') border-red-400 @else border-stone-300 @enderror">{{ old('body', $project->body) }}</textarea>
      @error('body')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        <p class="text-xs text-stone-400 mt-1">Plain text or basic HTML. Leave blank to skip the dedicated project page.</p>
      @enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Tags (comma-separated)</label>
      <input name="tags" value="{{ old('tags', $project->tags) }}" placeholder="Next.js, Stripe" class="w-full border rounded px-3 py-2 text-sm @error('tags') border-red-400 @else border-stone-300 @enderror">
      @error('tags')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Meta title (SEO)</label>
      <input name="meta_title" value="{{ old('meta_title', $project->meta_title) }}" class="w-full border rounded px-3 py-2 text-sm @error('meta_title') border-red-400 @else border-stone-300 @enderror">
      @error('meta_title')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        <p class="text-xs text-stone-400 mt-1">Falls back to the project name.</p>
      @enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Meta description (SEO)</label>
      <textarea name="meta_description" rows="2" maxlength="255" class="w-full border rounded px-3 py-2 text-sm @error('meta_description') border-red-400 @else border-stone-300 @enderror">{{ old('meta_description', $project->meta_description) }}</textarea>
      @error('meta_description')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @else
        <p class="text-xs text-stone-400 mt-1">Falls back to the description, then a body excerpt. Aim for 150–160 characters.</p>
      @enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Sort order</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $project->sort_order ?? 0) }}" class="w-32 border rounded px-3 py-2 text-sm @error('sort_order') border-red-400 @else border-stone-300 @enderror">
      @error('sort_order')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
