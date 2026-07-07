@extends('layouts.admin')
@section('title', 'Profile')
@section('content')
  <h1 class="text-2xl font-semibold mb-1">Profile & hero content</h1>
  <p class="text-stone-500 text-sm mb-8">This controls the nav, hero section, contact card, and docs page on your live site.</p>

  <form method="POST" action="{{ route('admin.profile.update') }}" class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-2xl">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Name</label>
        <input name="name" value="{{ old('name', $profile->name) }}" required class="w-full border rounded px-3 py-2 text-sm @error('name') border-red-400 @else border-stone-300 @enderror">
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">City</label>
        <input name="city" value="{{ old('city', $profile->city) }}" required class="w-full border rounded px-3 py-2 text-sm @error('city') border-red-400 @else border-stone-300 @enderror">
        @error('city')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Tagline (e.g. "Full-stack Web Developer")</label>
      <input name="tagline" value="{{ old('tagline', $profile->tagline) }}" required class="w-full border rounded px-3 py-2 text-sm @error('tagline') border-red-400 @else border-stone-300 @enderror">
      @error('tagline')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Hero headline</label>
      <input name="hero_headline" value="{{ old('hero_headline', $profile->hero_headline) }}" required class="w-full border rounded px-3 py-2 text-sm @error('hero_headline') border-red-400 @else border-stone-300 @enderror">
      @error('hero_headline')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Hero subtext</label>
      <textarea name="hero_subtext" rows="3" class="w-full border rounded px-3 py-2 text-sm @error('hero_subtext') border-red-400 @else border-stone-300 @enderror">{{ old('hero_subtext', $profile->hero_subtext) }}</textarea>
      @error('hero_subtext')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="available" value="1" {{ old('available', $profile->available) ? 'checked' : '' }}>
      Available for new projects
    </label>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Availability start (if booked)</label>
        <input name="availability_from" value="{{ old('availability_from', $profile->availability_from) }}" class="w-full border rounded px-3 py-2 text-sm @error('availability_from') border-red-400 @else border-stone-300 @enderror">
        @error('availability_from')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Rate</label>
        <input name="rate" value="{{ old('rate', $profile->rate) }}" class="w-full border rounded px-3 py-2 text-sm @error('rate') border-red-400 @else border-stone-300 @enderror">
        @error('rate')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Email</label>
        <input name="email" value="{{ old('email', $profile->email) }}" class="w-full border rounded px-3 py-2 text-sm @error('email') border-red-400 @else border-stone-300 @enderror">
        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">KVK number</label>
        <input name="kvk_number" value="{{ old('kvk_number', $profile->kvk_number) }}" class="w-full border rounded px-3 py-2 text-sm @error('kvk_number') border-red-400 @else border-stone-300 @enderror">
        @error('kvk_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">LinkedIn URL</label>
        <input name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" class="w-full border rounded px-3 py-2 text-sm @error('linkedin_url') border-red-400 @else border-stone-300 @enderror">
        @error('linkedin_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">GitHub URL</label>
        <input name="github_url" value="{{ old('github_url', $profile->github_url) }}" class="w-full border rounded px-3 py-2 text-sm @error('github_url') border-red-400 @else border-stone-300 @enderror">
        @error('github_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <fieldset class="border border-stone-200 rounded p-4 space-y-4">
      <legend class="text-xs font-medium text-stone-600 px-1">Nederlands (NL)</legend>
      <p class="text-xs text-stone-400">Optional. Falls back to the English text above when left blank.</p>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Tagline (NL)</label>
        <input name="tagline_nl" value="{{ old('tagline_nl', $profile->tagline_nl) }}" class="w-full border rounded px-3 py-2 text-sm @error('tagline_nl') border-red-400 @else border-stone-300 @enderror">
        @error('tagline_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Hero headline (NL)</label>
        <input name="hero_headline_nl" value="{{ old('hero_headline_nl', $profile->hero_headline_nl) }}" class="w-full border rounded px-3 py-2 text-sm @error('hero_headline_nl') border-red-400 @else border-stone-300 @enderror">
        @error('hero_headline_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Hero subtext (NL)</label>
        <textarea name="hero_subtext_nl" rows="3" class="w-full border rounded px-3 py-2 text-sm @error('hero_subtext_nl') border-red-400 @else border-stone-300 @enderror">{{ old('hero_subtext_nl', $profile->hero_subtext_nl) }}</textarea>
        @error('hero_subtext_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Docs intro paragraph (NL)</label>
        <textarea name="docs_intro_nl" rows="4" class="w-full border rounded px-3 py-2 text-sm @error('docs_intro_nl') border-red-400 @else border-stone-300 @enderror">{{ old('docs_intro_nl', $profile->docs_intro_nl) }}</textarea>
        @error('docs_intro_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta title (NL)</label>
        <input name="meta_title_nl" value="{{ old('meta_title_nl', $profile->meta_title_nl) }}" class="w-full border rounded px-3 py-2 text-sm @error('meta_title_nl') border-red-400 @else border-stone-300 @enderror">
        @error('meta_title_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta description (NL)</label>
        <textarea name="meta_description_nl" rows="2" class="w-full border rounded px-3 py-2 text-sm @error('meta_description_nl') border-red-400 @else border-stone-300 @enderror">{{ old('meta_description_nl', $profile->meta_description_nl) }}</textarea>
        @error('meta_description_nl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </fieldset>

    <fieldset class="border border-stone-200 rounded p-4 space-y-4">
      <legend class="text-xs font-medium text-stone-600 px-1">Docs page</legend>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Intro paragraph <span class="font-normal text-stone-400">(overrides the default lead on /docs)</span></label>
        <textarea name="docs_intro" rows="4" class="w-full border rounded px-3 py-2 text-sm @error('docs_intro') border-red-400 @else border-stone-300 @enderror">{{ old('docs_intro', $profile->docs_intro) }}</textarea>
        @error('docs_intro')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </fieldset>

    <fieldset class="border border-stone-200 rounded p-4 space-y-4">
      <legend class="text-xs font-medium text-stone-600 px-1">SEO</legend>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta title</label>
        <input name="meta_title" value="{{ old('meta_title', $profile->meta_title) }}" placeholder="defaults to name — tagline" class="w-full border rounded px-3 py-2 text-sm @error('meta_title') border-red-400 @else border-stone-300 @enderror">
        @error('meta_title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta description</label>
        <textarea name="meta_description" rows="2" placeholder="defaults to hero subtext" class="w-full border rounded px-3 py-2 text-sm @error('meta_description') border-red-400 @else border-stone-300 @enderror">{{ old('meta_description', $profile->meta_description) }}</textarea>
        @error('meta_description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </fieldset>

    <div class="pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save changes</button>
    </div>
  </form>
@endsection
