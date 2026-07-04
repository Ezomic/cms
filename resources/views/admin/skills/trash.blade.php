@extends('layouts.admin')
@section('title', 'Skills · Trash')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Skills · Trash</h1>
    <a href="{{ route('admin.skills.index') }}" class="text-sm text-stone-500 hover:text-orange-600">← Back to skills</a>
  </div>

  @forelse ($skills as $category => $items)
    <div class="mb-6">
      <div class="text-xs font-mono uppercase tracking-wide text-orange-600 mb-2">{{ $category }}</div>
      <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
        @foreach ($items as $skill)
          <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-6 py-3">
            <span class="text-sm">{{ $skill->name }}</span>
            <div class="flex gap-3 text-sm">
              <form method="POST" action="{{ route('admin.skills.restore', $skill->id) }}">
                @csrf
                <button class="text-stone-600 hover:text-orange-600">Restore</button>
              </form>
              <form method="POST" action="{{ route('admin.skills.forceDelete', $skill->id) }}" onsubmit="return confirm('Permanently delete this skill? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="text-stone-600 hover:text-red-600">Delete permanently</button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @empty
    <div class="bg-white border border-stone-200 rounded px-6 py-10 text-center text-sm text-stone-400">Trash is empty.</div>
  @endforelse
@endsection
