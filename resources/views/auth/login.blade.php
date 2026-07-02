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
        <input type="password" name="password" required
               class="w-full border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
      </div>
      <button type="submit" class="w-full bg-stone-900 text-white text-sm rounded px-3 py-2 hover:bg-orange-600 transition">
        Log in
      </button>
    </form>
    <p class="text-xs text-stone-400 text-center mt-4">Seeded admin: admin@example.com / password</p>
  </div>
</body>
</html>
