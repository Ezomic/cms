@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
  <h1 class="text-2xl font-semibold mb-1">Settings</h1>
  <p class="text-stone-500 text-sm mb-8">Signed in as {{ $user->email }}.</p>

  <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white border border-stone-200 rounded p-8 space-y-5 max-w-md">
    @csrf
    @method('PUT')

    <h2 class="text-sm font-medium">Change password</h2>

    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Current password</label>
      <input type="password" name="current_password" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">New password</label>
      <input type="password" name="password" required minlength="8" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Confirm new password</label>
      <input type="password" name="password_confirmation" required minlength="8" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>

    <div class="pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Update password</button>
    </div>
  </form>
@endsection
