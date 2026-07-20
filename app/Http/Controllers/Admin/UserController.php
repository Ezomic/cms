<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\InteractsWithCurrentUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use InteractsWithCurrentUser;

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Admin user created. They can sign in with an emailed login code, then register a passkey.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->forceFill($data)->save();

        return redirect()->route('admin.users.index')->with('status', 'Admin user updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === $this->currentUser()->id) {
            return back()->with('status', "You can't delete your own account while logged in as it.");
        }

        if (User::count() <= 1) {
            return back()->with('status', 'At least one admin account must remain.');
        }

        $user->delete();

        return back()->with('status', 'Admin user deleted.');
    }
}
