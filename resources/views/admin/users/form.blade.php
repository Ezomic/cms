@extends('layouts.admin')
@section('title', $user->exists ? 'Edit admin' : 'New admin')
@section('content')
  <h1 class="text-2xl font-semibold mb-8">{{ $user->exists ? 'Edit admin' : 'New admin' }}</h1>

  <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
        class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-md">
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Name</label>
      <input name="name" value="{{ old('name', $user->name) }}" required class="w-full border rounded px-3 py-2 text-sm @error('name') border-red-400 @else border-stone-300 @enderror">
      @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Email</label>
      <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border rounded px-3 py-2 text-sm @error('email') border-red-400 @else border-stone-300 @enderror">
      @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      @unless ($user->exists)
        <p class="text-xs text-stone-400 mt-1">Login is passwordless — they'll sign in with an emailed login code, then can register their own passkey.</p>
      @endunless
    </div>

    <div class="flex gap-3 pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Save</button>
      <a href="{{ route('admin.users.index') }}" class="text-sm text-stone-500 px-4 py-2">Cancel</a>
    </div>
  </form>
@endsection
