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

  <a href="{{ route('admin.profile.edit') }}" class="text-sm font-mono border border-stone-900 rounded px-4 py-2 inline-block hover:bg-stone-900 hover:text-white transition">
    Edit profile & hero content →
  </a>
@endsection
