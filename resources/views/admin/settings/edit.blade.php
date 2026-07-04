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
      <input id="current_password" type="password" name="current_password" required class="w-full border rounded px-3 py-2 text-sm @error('current_password') border-red-400 @else border-stone-300 @enderror">
      @error('current_password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">New password</label>
      <div class="relative">
        <input id="password" type="password" name="password" required minlength="8" class="w-full border rounded px-3 py-2 pr-16 text-sm @error('password') border-red-400 @else border-stone-300 @enderror">
        <button type="button" onclick="var p=document.getElementById('password'),c=document.getElementById('password_confirmation'),cur=document.getElementById('current_password');var v=p.type==='password';p.type=c.type=cur.type=v?'text':'password';this.textContent=v?'Hide':'Show';"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-stone-400 hover:text-stone-700">Show</button>
      </div>
      @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-xs font-medium text-stone-600 mb-1">Confirm new password</label>
      <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
    </div>

    <div class="pt-2">
      <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Update password</button>
    </div>
  </form>
@endsection
