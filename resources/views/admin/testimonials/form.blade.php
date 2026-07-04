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
      <textarea name="quote" rows="3" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">{{ old('quote', $testimonial->quote) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Author name</label>
        <input name="author_name" value="{{ old('author_name', $testimonial->author_name) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Author role</label>
        <input name="author_role" value="{{ old('author_role', $testimonial->author_role) }}" placeholder="CTO" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Company name</label>
      <input name="company_name" value="{{ old('company_name', $testimonial->company_name) }}" placeholder="Acme BV" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="featured" value="1" {{ old('featured', $testimonial->featured ?? true) ? 'checked' : '' }}>
      Show on live site (only one featured testimonial is displayed at a time)
    </label>

    <div class="flex gap-3 pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save</button>
      <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-stone-500 px-4 py-2">Cancel</a>
    </div>
  </form>
@endsection
