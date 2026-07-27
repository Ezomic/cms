<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\InteractsWithCurrentUser;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SecurityController extends Controller
{
    use InteractsWithCurrentUser;

    public function show(): InertiaResponse
    {
        return Inertia::render('Security/Show', [
            'passkeys' => $this->currentUser()->passkeys()->orderByDesc('created_at')->get()
                ->map(fn ($passkey) => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'authenticator' => $passkey->authenticator ?? 'Passkey',
                    'last_used' => $passkey->last_used_at ? 'last used '.$passkey->last_used_at->diffForHumans() : 'never used',
                ]),
        ]);
    }
}
