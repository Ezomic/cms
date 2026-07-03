@extends('layouts.admin')
@section('title', 'Admins')
@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-semibold">Admin users</h1>
    <a href="{{ route('admin.users.create') }}" class="text-sm bg-stone-900 text-white rounded px-4 py-2 hover:bg-orange-600 transition">+ New admin</a>
  </div>

  <div class="bg-white border border-stone-200 rounded divide-y divide-stone-200">
    @foreach ($users as $user)
      <div class="flex items-center justify-between px-6 py-4">
        <div>
          <div class="font-medium">{{ $user->name }} @if ($user->id === auth()->id())<span class="text-xs text-stone-400">(you)</span>@endif</div>
          <div class="text-xs text-stone-500">{{ $user->email }}</div>
        </div>
        <div class="flex gap-3 text-sm">
          <a href="{{ route('admin.users.edit', $user) }}" class="text-stone-600 hover:text-orange-600">Edit</a>
          <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this admin user?')">
            @csrf @method('DELETE')
            <button class="text-stone-600 hover:text-red-600">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@endsection
