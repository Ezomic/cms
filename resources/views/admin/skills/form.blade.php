@extends('layouts.admin')
@section('title', $skill->exists ? 'Edit skill' : 'New skill')
@section('content')
  <h1 class="text-2xl font-semibold mb-8">{{ $skill->exists ? 'Edit skill' : 'New skill' }}</h1>

  <form method="POST" action="{{ $skill->exists ? route('admin.skills.update', $skill) : route('admin.skills.store') }}"
        class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-xl">
    @csrf
    @if ($skill->exists) @method('PUT') @endif

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Category</label>
      <input name="category" list="categories" value="{{ old('category', $skill->category) }}" required class="w-full border rounded px-3 py-2 text-sm @error('category') border-red-400 @else border-stone-300 @enderror">
      <datalist id="categories">
        <option value="Frontend"></option>
        <option value="Backend"></option>
        <option value="Infra & Ops"></option>
      </datalist>
      @error('category')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Skill name</label>
      <input name="name" value="{{ old('name', $skill->name) }}" required class="w-full border rounded px-3 py-2 text-sm @error('name') border-red-400 @else border-stone-300 @enderror">
      @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Sort order</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $skill->sort_order ?? 0) }}" class="w-32 border rounded px-3 py-2 text-sm @error('sort_order') border-red-400 @else border-stone-300 @enderror">
      @error('sort_order')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3 pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save</button>
      <a href="{{ route('admin.skills.index') }}" class="text-sm text-stone-500 px-4 py-2">Cancel</a>
    </div>
  </form>
@endsection
