<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin login</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:Inter,system-ui,sans-serif;}</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-sm">
    <div class="font-mono text-sm mb-8 text-center">■ Portfolio CMS</div>
    <div class="bg-white border border-stone-200 rounded p-8 space-y-4">
      <div id="passkey-error" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2"></div>
      @if ($errors->any())
        <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2">
          {{ $errors->first() }}
        </div>
      @endif

      <a href="{{ route('sso.redirect') }}" class="block w-full bg-stone-900 text-white text-sm text-center rounded px-3 py-2 hover:bg-orange-600 transition">
        Sign in with Thijssensoftware
      </a>

      <div class="flex items-center gap-3 text-xs text-stone-400">
        <div class="flex-1 border-t border-stone-200"></div>
        or
        <div class="flex-1 border-t border-stone-200"></div>
      </div>

      <button type="button" id="passkey-login-btn" class="w-full border border-stone-300 text-stone-700 text-sm rounded px-3 py-2 hover:bg-stone-100 transition">
        Sign in with passkey
      </button>
      <p id="passkey-unsupported" class="hidden text-xs text-stone-400 text-center">Your browser doesn't support passkeys — use a login code instead.</p>

      <div class="flex items-center gap-3 text-xs text-stone-400">
        <div class="flex-1 border-t border-stone-200"></div>
        or
        <div class="flex-1 border-t border-stone-200"></div>
      </div>

      <form method="POST" action="{{ route('admin.login.code.send') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-stone-600 mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" required
                 class="w-full border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>
        <button type="submit" class="w-full border border-stone-300 text-stone-700 text-sm rounded px-3 py-2 hover:bg-stone-100 transition">
          Email me a login code
        </button>
      </form>
    </div>
  </div>

  <script>
    (function () {
      const btn = document.getElementById('passkey-login-btn');
      const errorEl = document.getElementById('passkey-error');
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const supported = typeof window.PublicKeyCredential !== 'undefined'
        && typeof PublicKeyCredential.parseRequestOptionsFromJSON === 'function';

      if (! supported) {
        btn.classList.add('hidden');
        document.getElementById('passkey-unsupported').classList.remove('hidden');
        return;
      }

      btn.addEventListener('click', async () => {
        errorEl.classList.add('hidden');

        try {
          const optionsRes = await fetch('{{ route('passkey.login-options') }}', {
            headers: { Accept: 'application/json' },
          });
          if (! optionsRes.ok) throw new Error();
          const { options } = await optionsRes.json();

          const publicKey = PublicKeyCredential.parseRequestOptionsFromJSON(options);
          const credential = await navigator.credentials.get({ publicKey });

          const loginRes = await fetch('{{ route('passkey.login') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ credential: credential.toJSON(), remember: true }),
          });
          if (! loginRes.ok) throw new Error();

          const { redirect } = await loginRes.json();
          window.location = redirect || '{{ route('admin.dashboard') }}';
        } catch (error) {
          errorEl.textContent = 'Passkey sign-in failed. Please try again or use a login code.';
          errorEl.classList.remove('hidden');
        }
      });
    })();
  </script>
</body>
</html>
