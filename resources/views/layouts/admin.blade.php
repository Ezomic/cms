<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin') · CMS</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>body{font-family:Inter,system-ui,sans-serif;}</style>
</head>
<body class="bg-stone-50 text-stone-900">

<div class="flex min-h-screen">
  <aside class="w-56 border-r border-stone-200 bg-white p-6 flex flex-col justify-between">
    <div>
      <div class="font-mono text-sm font-medium mb-8">■ Portfolio CMS</div>
      <nav class="space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.dashboard') ? 'bg-stone-100 font-medium' : '' }}">Dashboard</a>
        <a href="{{ route('admin.projects.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.projects.*') ? 'bg-stone-100 font-medium' : '' }}">Projects</a>
        <a href="{{ route('admin.testimonials.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.testimonials.*') ? 'bg-stone-100 font-medium' : '' }}">Testimonials</a>
        <a href="{{ route('admin.skills.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.skills.*') ? 'bg-stone-100 font-medium' : '' }}">Skills</a>
        <a href="{{ route('admin.profile.edit') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.profile.*') ? 'bg-stone-100 font-medium' : '' }}">Profile</a>
      </nav>
    </div>
    <div class="space-y-3">
      <a href="{{ route('home') }}" target="_blank" class="block text-xs text-stone-500 hover:text-orange-600">View live site →</a>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="text-xs text-stone-500 hover:text-orange-600">Log out</button>
      </form>
    </div>
  </aside>

  <main class="flex-1 p-10">
    @if (session('status'))
      <div class="mb-6 rounded border border-green-200 bg-green-50 text-green-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="mb-6 rounded border border-red-200 bg-red-50 text-red-800 text-sm px-4 py-3">
        <ul class="list-disc pl-4">
          @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </main>
</div>

</body>
</html>
