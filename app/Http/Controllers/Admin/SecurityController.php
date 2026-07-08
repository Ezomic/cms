<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.security.show', [
            'passkeys' => $request->user()->passkeys()->orderByDesc('created_at')->get(),
        ]);
    }
}
