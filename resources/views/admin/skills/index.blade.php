@extends('layouts.admin')
@section('title', 'Skills')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Skills</h1>
    <a href="{{ route('admin.skills.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New skill</a>
  </div>

  <form method="GET" class="mb-4">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or category…"
           class="w-full max-w-sm border border-stone-300 rounded px-3 py-2 text-sm">
  </form>

  @forelse ($skills as $category => $items)
    <div class="mb-6">
      <div class="text-xs font-mono uppercase tracking-wide text-orange-600 mb-2">{{ $category }}</div>
      <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
        @foreach ($items as $skill)
          <div class="flex items-center justify-between px-6 py-3">
            <span class="text-sm">{{ $skill->name }}</span>
            <div class="flex gap-3 text-sm">
              <a href="{{ route('admin.skills.edit', $skill) }}" class="text-stone-600 hover:text-orange-600">Edit</a>
              <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" onsubmit="return confirm('Delete this skill?')">
                @csrf @method('DELETE')
                <button class="text-stone-600 hover:text-red-600">Delete</button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @empty
    <div class="bg-white border border-stone-200 rounded px-6 py-10 text-center text-sm text-stone-400">
      {{ $search ? 'No skills match your search.' : 'No skills yet.' }}
    </div>
  @endforelse
@endsection
