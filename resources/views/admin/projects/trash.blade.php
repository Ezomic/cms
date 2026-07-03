@extends('layouts.admin')
@section('title', 'Projects · Trash')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Projects · Trash</h1>
    <a href="{{ route('admin.projects.index') }}" class="text-sm text-stone-500 hover:text-orange-600">← Back to projects</a>
  </div>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($projects as $project)
      <div class="flex items-center justify-between px-6 py-4">
        <div>
          <div class="font-medium">{{ $project->name }}</div>
          <div class="text-xs text-stone-500">{{ $project->year }} — {{ $project->client_name }}</div>
        </div>
        <div class="flex gap-3 text-sm">
          <form method="POST" action="{{ route('admin.projects.restore', $project->id) }}">
            @csrf
            <button class="text-stone-600 hover:text-orange-600">Restore</button>
          </form>
          <form method="POST" action="{{ route('admin.projects.forceDelete', $project->id) }}" onsubmit="return confirm('Permanently delete this project? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete permanently</button>
          </form>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">Trash is empty.</div>
    @endforelse
  </div>
@endsection
