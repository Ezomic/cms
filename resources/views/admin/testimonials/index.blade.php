@extends('layouts.admin')
@section('title', 'Testimonials')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Testimonials</h1>
    <a href="{{ route('admin.testimonials.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New testimonial</a>
  </div>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($testimonials as $t)
      <div class="flex items-center justify-between px-6 py-4">
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
      <div class="px-6 py-10 text-center text-sm text-stone-400">No testimonials yet.</div>
    @endforelse
  </div>
@endsection
