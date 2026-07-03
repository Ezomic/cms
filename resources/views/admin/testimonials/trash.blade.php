@extends('layouts.admin')
@section('title', 'Testimonials · Trash')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Testimonials · Trash</h1>
    <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-stone-500 hover:text-orange-600">← Back to testimonials</a>
  </div>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($testimonials as $t)
      <div class="flex items-center justify-between px-6 py-4">
        <div class="max-w-lg">
          <div class="text-sm italic">"{{ \Illuminate\Support\Str::limit($t->quote, 90) }}"</div>
          <div class="text-xs text-stone-500 mt-1">{{ $t->author_name }}</div>
        </div>
        <div class="flex gap-3 text-sm">
          <form method="POST" action="{{ route('admin.testimonials.restore', $t->id) }}">
            @csrf
            <button class="text-stone-600 hover:text-orange-600">Restore</button>
          </form>
          <form method="POST" action="{{ route('admin.testimonials.forceDelete', $t->id) }}" onsubmit="return confirm('Permanently delete this testimonial? This cannot be undone.')">
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
