@extends('layouts.admin')
@section('title', 'Projects')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Projects</h1>
    <div class="flex items-center gap-4">
      <a href="{{ route('admin.projects.trash') }}" class="text-sm text-stone-500 hover:text-orange-600">Trash</a>
      <a href="{{ route('admin.projects.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New project</a>
    </div>
  </div>

  <form method="GET" class="mb-4 flex items-center gap-3">
    <div class="relative w-full max-w-sm">
      <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or client…"
             class="w-full border border-stone-300 rounded px-3 py-2 pr-8 text-sm">
      @if ($search)
        <a href="{{ route('admin.projects.index') }}" aria-label="Clear search" class="absolute right-2 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700">&times;</a>
      @endif
    </div>
    @if ($search)
      <span class="text-xs text-stone-400">{{ $projects->total() }} {{ Str::plural('result', $projects->total()) }}</span>
    @endif
  </form>

  @if ($projects->count() > 1)
    <p class="text-xs text-stone-400 mb-2">Drag the handle to reorder (within this page).</p>
  @endif

  <div id="project-list" class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($projects as $project)
      <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-6 py-4" data-id="{{ $project->id }}">
        <div class="flex items-center gap-4">
          <span class="drag-handle cursor-grab text-stone-300 select-none">⠿</span>
          @if ($project->image)
            <img src="{{ $project->imageUrl() }}" alt="" class="w-14 h-10 object-cover rounded border border-stone-200">
          @else
            <div class="w-14 h-10 rounded border border-dashed border-stone-200"></div>
          @endif
          <div>
            <div class="font-medium flex items-center gap-2">
              {{ $project->name }}
              @if ($project->published)
                <span class="text-[10px] font-mono uppercase tracking-wide text-green-700 bg-green-50 border border-green-200 rounded px-1.5 py-0.5">Published</span>
              @else
                <span class="text-[10px] font-mono uppercase tracking-wide text-stone-500 bg-stone-100 border border-stone-200 rounded px-1.5 py-0.5">Draft</span>
              @endif
            </div>
            <div class="text-xs text-stone-500">{{ $project->year }} — {{ $project->client_name }}</div>
          </div>
        </div>
        <div class="flex gap-3 text-sm">
          @if ($project->body)
            <a href="{{ url('/work/'.$project->slug) }}" target="_blank" class="text-stone-600 hover:text-orange-600">View</a>
          @endif
          <a href="{{ route('admin.projects.edit', $project) }}" class="text-stone-600 hover:text-orange-600">Edit</a>
          <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">
        {{ $search ? 'No projects match your search.' : 'No projects yet.' }}
      </div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $projects->links() }}
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
          })
            .then(r => { if (!r.ok) throw new Error(); adminToast('Order saved'); })
            .catch(() => adminToast('Could not save the new order — reload the page and try again.', true));
        },
      });
    }
  </script>
@endsection
