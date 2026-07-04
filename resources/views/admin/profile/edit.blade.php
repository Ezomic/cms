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
        <input name="name" value="{{ old('name', $profile->name) }}" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">City</label>
        <input name="city" value="{{ old('city', $profile->city) }}" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Tagline (e.g. "Full-stack Web Developer")</label>
      <input name="tagline" value="{{ old('tagline', $profile->tagline) }}" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Hero headline</label>
      <input name="hero_headline" value="{{ old('hero_headline', $profile->hero_headline) }}" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Hero subtext</label>
      <textarea name="hero_subtext" rows="3" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">{{ old('hero_subtext', $profile->hero_subtext) }}</textarea>
    </div>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="available" value="1" {{ old('available', $profile->available) ? 'checked' : '' }}>
      Available for new projects
    </label>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Availability start (if booked)</label>
        <input name="availability_from" value="{{ old('availability_from', $profile->availability_from) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Rate</label>
        <input name="rate" value="{{ old('rate', $profile->rate) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Email</label>
        <input name="email" value="{{ old('email', $profile->email) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">KVK number</label>
        <input name="kvk_number" value="{{ old('kvk_number', $profile->kvk_number) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">LinkedIn URL</label>
        <input name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">GitHub URL</label>
        <input name="github_url" value="{{ old('github_url', $profile->github_url) }}" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <fieldset class="border border-stone-200 rounded p-4 space-y-4">
      <legend class="text-xs font-medium text-stone-600 px-1">Docs page</legend>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Intro paragraph <span class="font-normal text-stone-400">(overrides the default lead on /docs)</span></label>
        <textarea name="docs_intro" rows="4" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">{{ old('docs_intro', $profile->docs_intro) }}</textarea>
      </div>
    </fieldset>

    <fieldset class="border border-stone-200 rounded p-4 space-y-4">
      <legend class="text-xs font-medium text-stone-600 px-1">SEO</legend>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta title</label>
        <input name="meta_title" value="{{ old('meta_title', $profile->meta_title) }}" placeholder="defaults to name — tagline" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Meta description</label>
        <textarea name="meta_description" rows="2" placeholder="defaults to hero subtext" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">{{ old('meta_description', $profile->meta_description) }}</textarea>
      </div>
    </fieldset>

    <div class="pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save changes</button>
    </div>
  </form>
@endsection
