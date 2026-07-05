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
          <input type="password" name="current_password" required class="w-full border rounded px-3 py-2 text-sm @error('current_password') border-red-400 @else border-stone-300 @enderror">
          @error('current_password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-red-600 transition">Disable two-factor authentication</button>
      </form>
    </div>
  @elseif ($otpAuthUri)
    <div class="bg-white border border-stone-200 rounded p-8 max-w-lg space-y-6">
      <div>
        <div class="flex items-center gap-2 mb-4">
          <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 text-orange-600 text-xs font-bold font-mono">1</span>
          <span class="text-sm font-medium">Scan the QR code with your authenticator app</span>
        </div>
        <p class="text-xs text-stone-500 mb-4">Use Google Authenticator, 1Password, Authy, or any TOTP-compatible app.</p>
        <div class="flex gap-6 items-start">
          <canvas id="qr-canvas" width="160" height="160" class="border border-stone-200 rounded flex-shrink-0"></canvas>
          <div class="flex-1">
            <p class="text-xs text-stone-500 mb-2">Can't scan? Enter this key manually:</p>
            <div class="font-mono text-xs bg-stone-50 border border-stone-200 rounded p-3 break-all select-all">{{ $user->two_factor_secret }}</div>
          </div>
        </div>
      </div>

      <div>
        <div class="flex items-center gap-2 mb-4">
          <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 text-orange-600 text-xs font-bold font-mono">2</span>
          <span class="text-sm font-medium">Enter the 6-digit code to confirm</span>
        </div>
        <form method="POST" action="{{ route('admin.two-factor.confirm') }}" class="space-y-4">
          @csrf
          <input type="text" name="code" required autofocus inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
            placeholder="000000"
            class="w-32 border rounded px-3 py-2 text-sm font-mono tracking-widest text-center @error('code') border-red-400 @else border-stone-300 @enderror">
          @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
          <div>
            <button class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">Confirm and enable →</button>
          </div>
        </form>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
    <script>
      new QRious({
        element: document.getElementById('qr-canvas'),
        value: {{ Js::from($otpAuthUri) }},
        size: 160,
        background: '#ffffff',
        foreground: '#17181A',
        level: 'M',
      });
    </script>
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
