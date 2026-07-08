@extends('layouts.admin')
@section('title', 'Security')
@section('content')
  <h1 class="text-2xl font-semibold mb-1">Security</h1>
  <p class="text-stone-500 text-sm mb-8">Sign in with a passkey instead of a password — fingerprint, face, or device PIN.</p>

  <div class="bg-white border border-stone-200 rounded p-8 max-w-lg space-y-6">
    <div id="passkey-error" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2"></div>

    <div class="divide-y divide-stone-200">
      @forelse ($passkeys as $passkey)
        <div class="flex items-center justify-between py-3" data-passkey-row="{{ $passkey->id }}">
          <div>
            <div class="text-sm font-medium">{{ $passkey->name }}</div>
            <div class="text-xs text-stone-500">
              {{ $passkey->authenticator ?? 'Passkey' }} ·
              {{ $passkey->last_used_at ? 'last used '.$passkey->last_used_at->diffForHumans() : 'never used' }}
            </div>
          </div>
          <button type="button" class="text-sm text-stone-500 hover:text-red-600" data-delete-passkey="{{ $passkey->id }}">Remove</button>
        </div>
      @empty
        <p class="text-sm text-stone-500 py-3">No passkeys registered yet.</p>
      @endforelse
    </div>

    <button type="button" id="add-passkey-btn" class="bg-stone-900 text-white text-sm rounded px-4 py-2 hover:bg-orange-600 transition">
      + Add a passkey
    </button>
    <p id="passkey-unsupported" class="hidden text-xs text-stone-400">Your browser doesn't support passkeys.</p>
  </div>

  <script>
    (function () {
      const errorEl = document.getElementById('passkey-error');
      const addBtn = document.getElementById('add-passkey-btn');
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const supported = typeof window.PublicKeyCredential !== 'undefined'
        && typeof PublicKeyCredential.parseCreationOptionsFromJSON === 'function';

      if (! supported) {
        addBtn.classList.add('hidden');
        document.getElementById('passkey-unsupported').classList.remove('hidden');
        return;
      }

      function showError(message) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
      }

      addBtn.addEventListener('click', async () => {
        errorEl.classList.add('hidden');

        const name = prompt('Name this passkey (e.g. "MacBook Touch ID")');
        if (! name) return;

        try {
          const optionsRes = await fetch('{{ route('passkey.registration-options') }}', {
            headers: { Accept: 'application/json' },
          });
          if (! optionsRes.ok) throw new Error();
          const { options } = await optionsRes.json();

          const publicKey = PublicKeyCredential.parseCreationOptionsFromJSON(options);
          const credential = await navigator.credentials.create({ publicKey });

          const storeRes = await fetch('{{ route('passkey.store') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ name, credential: credential.toJSON() }),
          });
          if (! storeRes.ok) throw new Error();

          window.location.reload();
        } catch (error) {
          showError('Could not register the passkey. Please try again.');
        }
      });

      document.querySelectorAll('[data-delete-passkey]').forEach((button) => {
        button.addEventListener('click', async () => {
          if (! confirm('Remove this passkey?')) return;

          const id = button.dataset.deletePasskey;

          try {
            const res = await fetch(`{{ url('/user/passkeys') }}/${id}`, {
              method: 'DELETE',
              headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
              },
            });
            if (! res.ok) throw new Error();

            document.querySelector(`[data-passkey-row="${id}"]`).remove();
          } catch (error) {
            showError('Could not remove the passkey. Please try again.');
          }
        });
      });
    })();
  </script>
@endsection
