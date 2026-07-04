@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Messages</h1>
  </div>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @forelse ($submissions as $submission)
      <div class="px-6 py-4 {{ $submission->read_at ? '' : 'bg-orange-50/40' }}">
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
          <div>
            <div class="flex items-center gap-2">
              @unless ($submission->read_at)
                <span class="w-2 h-2 rounded-full bg-orange-600 shrink-0" aria-label="Unread"></span>
              @endunless
              <span class="font-medium">{{ $submission->name }}</span>
              <a href="mailto:{{ $submission->email }}" class="text-sm text-stone-500 hover:text-orange-600">{{ $submission->email }}</a>
            </div>
            <div class="text-xs text-stone-400 font-mono mt-1">
              {{ $submission->created_at->format('Y-m-d H:i') }}
              @if ($submission->company) · {{ $submission->company }} @endif
              @if ($submission->budget) · {{ $submission->budget }} @endif
            </div>
          </div>
          <div class="flex gap-3 text-sm shrink-0">
            @if ($submission->read_at)
              <form method="POST" action="{{ route('admin.contact-submissions.unread', $submission) }}">
                @csrf
                <button class="text-stone-600 hover:text-orange-600">Mark unread</button>
              </form>
            @else
              <form method="POST" action="{{ route('admin.contact-submissions.read', $submission) }}">
                @csrf
                <button class="text-stone-600 hover:text-orange-600">Mark read</button>
              </form>
            @endif
            <form method="POST" action="{{ route('admin.contact-submissions.destroy', $submission) }}" onsubmit="return confirm('Delete this message?')">
              @csrf @method('DELETE')
              <button class="text-stone-600 hover:text-red-600">Delete</button>
            </form>
          </div>
        </div>
        <p class="text-sm text-stone-700 mt-3 whitespace-pre-line">{{ $submission->message }}</p>
      </div>
    @empty
      <div class="px-6 py-10 text-center text-sm text-stone-400">No messages yet.</div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $submissions->links() }}
  </div>
@endsection
