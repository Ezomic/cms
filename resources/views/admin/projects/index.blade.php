@extends('layouts.admin')
@section('title', 'Projects')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Projects</h1>
    <a href="{{ route('admin.projects.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New project</a>
  </div>

  @if ($projects->count() > 1)
    <p class="text-xs text-stone-400 mb-2">Drag the handle to reorder.</p>
  @endif

  <div id="project-list" class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($projects as $project)
      <div class="flex items-center justify-between px-6 py-4" data-id="{{ $project->id }}">
        <div class="flex items-center gap-4">
          <span class="drag-handle cursor-grab text-stone-300 select-none">⠿</span>
          @if ($project->image)
            <img src="{{ $project->imageUrl() }}" alt="" class="w-14 h-10 object-cover rounded border border-stone-200">
          @else
            <div class="w-14 h-10 rounded border border-dashed border-stone-200"></div>
          @endif
          <div>
            <div class="font-medium">{{ $project->name }}</div>
            <div class="text-xs text-stone-500">{{ $project->year }} — {{ $project->client_name }}</div>
          </div>
        </div>
        <div class="flex gap-3 text-sm">
          <a href="{{ route('admin.projects.edit', $project) }}" class="text-stone-600 hover:text-orange-600">Edit</a>
          <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">No projects yet.</div>
    @endforelse
  </div>

  <script>
    const list = document.getElementById('project-list');
    if (list) {
      new Sortable(list, {
        handle: '.drag-handle',
        animation: 150,
        onEnd() {
          const ids = [...list.children].map(el => el.dataset.id);
          fetch('{{ route('admin.projects.reorder') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ ids }),
          });
        },
      });
    }
  </script>
@endsection
