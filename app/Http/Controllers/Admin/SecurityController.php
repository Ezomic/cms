<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\InteractsWithCurrentUser;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SecurityController extends Controller
{
    use InteractsWithCurrentUser;

    public function show(): View
    {
        return view('admin.security.show', [
            'passkeys' => $this->currentUser()->passkeys()->orderByDesc('created_at')->get(),
        ]);
    }
}
