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

<div class="flex min-h-screen flex-col md:flex-row">
  <aside class="w-full md:w-56 border-b md:border-b-0 md:border-r border-stone-200 bg-white p-4 md:p-6 flex flex-col md:justify-between">
    <div>
      <div class="flex items-center justify-between md:mb-8">
        <div class="font-mono text-sm font-medium">■ Portfolio CMS</div>
        <button id="admin-nav-toggle" type="button" class="md:hidden border border-stone-300 rounded px-3 py-1.5 text-sm text-stone-600" aria-expanded="false" aria-controls="admin-nav">Menu</button>
      </div>
      <nav id="admin-nav" class="hidden md:block space-y-1 text-sm mt-4 md:mt-0">
        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.dashboard') ? 'bg-stone-100 font-medium' : '' }}">Dashboard</a>
        <a href="{{ route('admin.projects.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.projects.*') ? 'bg-stone-100 font-medium' : '' }}">Projects</a>
        <a href="{{ route('admin.testimonials.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.testimonials.*') ? 'bg-stone-100 font-medium' : '' }}">Testimonials</a>
        <a href="{{ route('admin.contact-submissions.index') }}" class="flex items-center justify-between px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.contact-submissions.*') ? 'bg-stone-100 font-medium' : '' }}">
          Messages
          @php($unread = \App\Models\ContactSubmission::whereNull('read_at')->count())
          @if ($unread)
            <span class="text-[10px] font-mono bg-orange-600 text-white rounded-full px-1.5 py-0.5 leading-none">{{ $unread }}</span>
          @endif
        </a>
        <a href="{{ route('admin.skills.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.skills.*') ? 'bg-stone-100 font-medium' : '' }}">Skills</a>
        <a href="{{ route('admin.profile.edit') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.profile.*') ? 'bg-stone-100 font-medium' : '' }}">Profile</a>
        <a href="{{ route('admin.settings.edit') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.settings.*') ? 'bg-stone-100 font-medium' : '' }}">Settings</a>
        <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.users.*') ? 'bg-stone-100 font-medium' : '' }}">Admins</a>
        <a href="{{ route('admin.two-factor.show') }}" class="block px-3 py-2 rounded hover:bg-stone-100 {{ request()->routeIs('admin.two-factor.show') ? 'bg-stone-100 font-medium' : '' }}">Security</a>
      </nav>
    </div>
    <div id="admin-nav-footer" class="hidden md:block space-y-3 mt-4 md:mt-0 pt-4 md:pt-0 border-t md:border-t-0 border-stone-200">
      <a href="{{ route('home') }}" target="_blank" class="block text-xs text-stone-500 hover:text-orange-600">View live site →</a>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="text-xs text-stone-500 hover:text-orange-600">Log out</button>
      </form>
    </div>
  </aside>

  <main class="flex-1 p-4 sm:p-6 md:p-10">
    @if (session('status'))
      <div class="mb-6 rounded border border-green-200 bg-green-50 text-green-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    @yield('content')
  </main>
</div>

<div id="admin-toast" role="status" aria-live="polite" class="hidden fixed bottom-4 right-4 z-50 rounded px-4 py-2 text-sm shadow-lg"></div>

<script>
  window.adminToast = function(msg, isError){
    var t = document.getElementById('admin-toast');
    t.textContent = msg;
    t.className = 'fixed bottom-4 right-4 z-50 rounded px-4 py-2 text-sm shadow-lg text-white ' + (isError ? 'bg-red-600' : 'bg-stone-900');
    clearTimeout(t._timer);
    t._timer = setTimeout(function(){ t.classList.add('hidden'); }, isError ? 6000 : 2500);
  };

  (function(){
    var toggle = document.getElementById('admin-nav-toggle');
    var nav = document.getElementById('admin-nav');
    var footer = document.getElementById('admin-nav-footer');
    toggle.addEventListener('click', function(){
      var open = nav.classList.toggle('hidden') === false;
      footer.classList.toggle('hidden', !open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  })();
</script>

</body>
</html>
