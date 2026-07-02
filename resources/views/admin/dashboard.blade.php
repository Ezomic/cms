@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
  <h1 class="text-2xl font-semibold mb-1">Dashboard</h1>
  <p class="text-stone-500 text-sm mb-8">Manage the content shown on your public site.</p>

  <div class="grid grid-cols-3 gap-4 mb-10">
    <a href="{{ route('admin.projects.index') }}" class="border border-stone-200 rounded p-6 bg-white hover:border-orange-400 transition">
      <div class="text-3xl font-semibold">{{ $projectCount }}</div>
      <div class="text-sm text-stone-500 mt-1">Projects</div>
    </a>
    <a href="{{ route('admin.testimonials.index') }}" class="border border-stone-200 rounded p-6 bg-white hover:border-orange-400 transition">
      <div class="text-3xl font-semibold">{{ $testimonialCount }}</div>
      <div class="text-sm text-stone-500 mt-1">Testimonials</div>
    </a>
    <a href="{{ route('admin.skills.index') }}" class="border border-stone-200 rounded p-6 bg-white hover:border-orange-400 transition">
      <div class="text-3xl font-semibold">{{ $skillCount }}</div>
      <div class="text-sm text-stone-500 mt-1">Skills</div>
    </a>
  </div>

  <a href="{{ route('admin.profile.edit') }}" class="text-sm font-mono border border-stone-900 rounded px-4 py-2 inline-block hover:bg-stone-900 hover:text-white transition mb-10">
    Edit profile & hero content →
  </a>

  <h2 class="text-sm font-medium text-stone-600 mb-3">Recent activity</h2>
  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200 max-w-xl">
    @forelse ($activity as $entry)
      <div class="px-4 py-3 text-sm">
        <span class="text-stone-500">{{ $entry->user->name ?? 'Someone' }}</span>
        {{ $entry->action }}
        <span class="font-medium">{{ $entry->subject_type }}</span>
        "{{ $entry->subject_label }}"
        <div class="text-xs text-stone-400">{{ $entry->created_at->diffForHumans() }}</div>
      </div>
    @empty
      <div class="px-4 py-6 text-center text-sm text-stone-400">No activity yet.</div>
    @endforelse
  </div>
@endsection
