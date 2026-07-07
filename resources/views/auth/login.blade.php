<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin login</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:Inter,system-ui,sans-serif;}</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-sm">
    <div class="font-mono text-sm mb-8 text-center">■ Portfolio CMS</div>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="bg-white border border-stone-200 rounded p-8 space-y-4">
      @csrf
      @if ($errors->any())
        <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2">
          {{ $errors->first() }}
        </div>
      @endif
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-stone-600 mb-1">Password</label>
        <div class="relative">
          <input id="password" type="password" name="password" required
                 class="w-full border border-stone-300 rounded px-3 py-2 pr-16 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
          <button type="button" onclick="var i=document.getElementById('password');var v=i.type==='password';i.type=v?'text':'password';this.textContent=v?'Hide':'Show';"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-stone-400 hover:text-stone-700">Show</button>
        </div>
      </div>
      <button type="submit" class="w-full bg-stone-900 text-white text-sm rounded px-3 py-2 hover:bg-orange-600 transition">
        Log in
      </button>
    </form>
  </div>
</body>
</html>
