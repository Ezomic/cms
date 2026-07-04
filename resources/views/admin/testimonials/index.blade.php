@extends('layouts.admin')
@section('title', 'Testimonials')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Testimonials</h1>
    <div class="flex items-center gap-4">
      <a href="{{ route('admin.testimonials.trash') }}" class="text-sm text-stone-500 hover:text-orange-600">Trash</a>
      <a href="{{ route('admin.testimonials.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New testimonial</a>
    </div>
  </div>

  <form method="GET" class="mb-4 flex items-center gap-3">
    <div class="relative w-full max-w-sm">
      <input type="text" name="search" value="{{ $search }}" placeholder="Search by quote or author…"
             class="w-full border border-stone-300 rounded px-3 py-2 pr-8 text-sm">
      @if ($search)
        <a href="{{ route('admin.testimonials.index') }}" aria-label="Clear search" class="absolute right-2 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700">&times;</a>
      @endif
    </div>
    @if ($search)
      <span class="text-xs text-stone-400">{{ $testimonials->total() }} {{ Str::plural('result', $testimonials->total()) }}</span>
    @endif
  </form>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($testimonials as $t)
      <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-6 py-4">
        <div class="max-w-lg">
          <div class="text-sm italic">"{{ \Illuminate\Support\Str::limit($t->quote, 90) }}"</div>
          <div class="text-xs text-stone-500 mt-1">{{ $t->author_name }} — {{ $t->featured ? 'Featured on site' : 'Hidden' }}</div>
        </div>
        <div class="flex gap-3 text-sm">
          <a href="{{ route('admin.testimonials.edit', $t) }}" class="text-stone-600 hover:text-orange-600">Edit</a>
          <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('Delete this testimonial?')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">
        {{ $search ? 'No testimonials match your search.' : 'No testimonials yet.' }}
      </div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $testimonials->links() }}
  </div>
@endsection
