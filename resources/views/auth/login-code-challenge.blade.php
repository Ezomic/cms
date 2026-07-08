<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enter login code</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:Inter,system-ui,sans-serif;}</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-sm">
    <div class="font-mono text-sm mb-8 text-center">■ Portfolio CMS</div>
    <form method="POST" action="{{ route('admin.login.code.verify') }}" class="bg-white border border-stone-200 rounded p-8 space-y-4">
      @csrf
      <h1 class="text-lg font-semibold">Enter your login code</h1>
      @if (session('status'))
        <p class="text-sm text-stone-500">{{ session('status') }}</p>
      @endif
      @if ($errors->any())
        <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2">
          {{ $errors->first() }}
        </div>
      @endif
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Code</label>
        <input type="text" name="code" value="{{ old('code') }}" required autofocus autocomplete="one-time-code"
               inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
               class="w-full border border-stone-300 rounded px-3 py-2 text-sm tracking-widest focus:outline-none focus:ring-2 focus:ring-orange-500">
      </div>
      <button type="submit" class="w-full bg-stone-900 text-white text-sm rounded px-3 py-2 hover:bg-orange-600 transition">
        Verify
      </button>
      <a href="{{ route('admin.login') }}" class="block text-center text-xs text-stone-400 hover:text-stone-700">Back to login</a>
    </form>
  </div>
</body>
</html>
