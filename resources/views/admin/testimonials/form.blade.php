@extends('layouts.admin')
@section('title', $testimonial->exists ? 'Edit testimonial' : 'New testimonial')
@section('content')
  <h1 class="text-2xl font-semibold mb-8">{{ $testimonial->exists ? 'Edit testimonial' : 'New testimonial' }}</h1>

  <form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}"
        class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-xl">
    @csrf
    @if ($testimonial->exists) @method('PUT') @endif

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Quote</label>
      <textarea name="quote" rows="3" required class="w-full border rounded px-3 py-2 text-sm @error('quote') border-red-400 @else border-stone-300 @enderror">{{ old('quote', $testimonial->quote) }}</textarea>
      @error('quote')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Author name</label>
        <input name="author_name" value="{{ old('author_name', $testimonial->author_name) }}" class="w-full border rounded px-3 py-2 text-sm @error('author_name') border-red-400 @else border-stone-300 @enderror">
        @error('author_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Author role</label>
        <input name="author_role" value="{{ old('author_role', $testimonial->author_role) }}" placeholder="CTO" class="w-full border rounded px-3 py-2 text-sm @error('author_role') border-red-400 @else border-stone-300 @enderror">
        @error('author_role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Company name</label>
      <input name="company_name" value="{{ old('company_name', $testimonial->company_name) }}" placeholder="Acme BV" class="w-full border rounded px-3 py-2 text-sm @error('company_name') border-red-400 @else border-stone-300 @enderror">
      @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <fieldset class="border border-stone-200 rounded p-4 space-y-4">
      <legend class="text-xs font-medium text-stone-600 px-1">Nederlands (NL)</legend>
      <p class="text-xs text-stone-400">Optional. Falls back to the English quote above when left blank.</p>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Quote (NL)</label>
        <textarea name="quote_nl" rows="3" class="w-full border rounded px-3 py-2 text-sm @error('quote_nl') border-red-400 @else border-stone-300 @enderror">{{ old('quote_nl', $testimonial->quote_nl) }}</textarea>
        @error('quote_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </fieldset>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="featured" value="1" {{ old('featured', $testimonial->featured ?? true) ? 'checked' : '' }}>
      Show on live site (appears in the homepage testimonial carousel)
    </label>

    <div class="flex gap-3 pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save</button>
      <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-stone-500 px-4 py-2">Cancel</a>
    </div>
  </form>
@endsection
