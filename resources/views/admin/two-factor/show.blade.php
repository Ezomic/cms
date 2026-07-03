@extends('layouts.admin')
@section('title', 'Two-factor authentication')
@section('content')
  <h1 class="text-2xl font-semibold mb-1">Two-factor authentication</h1>
  <p class="text-stone-500 text-sm mb-8">Require a code from an authenticator app in addition to your password.</p>

  @if ($recoveryCodes)
    <div class="bg-white border border-orange-300 rounded p-8 max-w-lg mb-8">
      <h2 class="text-sm font-medium mb-2">Save your recovery codes</h2>
      <p class="text-xs text-stone-500 mb-4">Each code can be used once to log in if you lose access to your authenticator app. They won't be shown again.</p>
      <div class="font-mono text-sm bg-stone-50 border border-stone-200 rounded p-4 space-y-1">
        @foreach ($recoveryCodes as $code)
          <div>{{ $code }}</div>
        @endforeach
      </div>
    </div>
  @endif

  @if ($user->hasTwoFactorEnabled())
    <div class="bg-white border border-stone-200 rounded p-8 max-w-lg space-y-4">
      <p class="text-sm text-green-700">Two-factor authentication is enabled.</p>
      <form method="POST" action="{{ route('admin.two-factor.disable') }}" class="space-y-4">
        @csrf
        @method('DELETE')
        <div>
          <label class="block text-xs font-medium text-stone-600 mb-1">Confirm your password to disable</label>
          <input type="password" name="current_password" required class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
        </div>
        <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-red-600 transition">Disable two-factor authentication</button>
      </form>
    </div>
  @elseif ($otpAuthUri)
    <div class="bg-white border border-stone-200 rounded p-8 max-w-lg space-y-4">
      <p class="text-sm">Add this account to your authenticator app (Google Authenticator, 1Password, Authy…), then enter the 6-digit code it shows to confirm.</p>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Manual entry URI</label>
        <input type="text" readonly value="{{ $otpAuthUri }}" class="w-full border border-stone-300 rounded px-3 py-2 text-xs font-mono bg-stone-50">
      </div>
      <form method="POST" action="{{ route('admin.two-factor.confirm') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-stone-600 mb-1">6-digit code</label>
          <input type="text" name="code" required autofocus class="w-full border border-stone-300 rounded px-3 py-2 text-sm">
        </div>
        <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Confirm and enable</button>
      </form>
    </div>
  @else
    <div class="bg-white border border-stone-200 rounded p-8 max-w-lg">
      <p class="text-sm text-stone-500 mb-4">Two-factor authentication is currently disabled.</p>
      <form method="POST" action="{{ route('admin.two-factor.enable') }}">
        @csrf
        <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Enable two-factor authentication</button>
      </form>
    </div>
  @endif
@endsection
