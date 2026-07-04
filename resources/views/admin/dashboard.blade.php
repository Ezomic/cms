@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
  <h1 class="text-2xl font-semibold mb-1">Dashboard</h1>
  <p class="text-stone-500 text-sm mb-8">Manage the content shown on your public site.</p>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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
    <div class="border border-stone-200 rounded p-6 bg-white">
      <div class="text-3xl font-semibold">{{ $contactCount }}</div>
      <div class="text-sm text-stone-500 mt-1">Contact submissions</div>
    </div>
  </div>

  {{-- Page views sparkline --}}
  <div class="border border-stone-200 rounded bg-white mb-8">
    <div class="px-6 pt-6 pb-4 flex items-end justify-between">
      <div>
        <div class="text-3xl font-semibold">{{ $pageViewCount }}</div>
        <div class="text-sm text-stone-500 mt-1">Page views (all-time)</div>
      </div>
      <div class="text-xs text-stone-400 font-mono">Last 30 days</div>
    </div>
    @php
      $maxViews = $sparkline->max() ?: 1;
      $points = $sparkline->values()->map(function($v, $i) use ($maxViews) {
        $x = $i * (560 / 29);
        $y = 60 - ($v / $maxViews) * 50;
        return "{$x},{$y}";
      })->implode(' ');
    @endphp
    <div class="px-6 pb-6">
      <svg viewBox="0 0 560 64" class="w-full h-16" preserveAspectRatio="none">
        <polyline points="{{ $points }}" fill="none" stroke="#ea580c" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/>
      </svg>
    </div>
  </div>

  {{-- Top pages --}}
  <div class="border border-stone-200 rounded bg-white mb-8 max-w-lg">
    <div class="px-6 py-4 border-b border-stone-100 text-sm font-medium">Top pages (all-time)</div>
    @forelse ($topPaths as $row)
      <div class="px-6 py-3 flex justify-between text-sm border-b border-stone-100 last:border-0">
        <span class="font-mono text-stone-600">{{ $row->path }}</span>
        <span class="text-stone-400">{{ number_format($row->views) }}</span>
      </div>
    @empty
      <div class="px-6 py-4 text-sm text-stone-400">No page views recorded yet.</div>
    @endforelse
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
