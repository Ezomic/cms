<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.user_id')) {
            return redirect()->route('admin.login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('two_factor.user_id');
        $user = is_int($userId) ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($this->codeIsValid($user, $request->string('code')->toString())) {
            Auth::login($user, (bool) $request->session()->get('two_factor.remember'));
            $request->session()->forget(['two_factor.user_id', 'two_factor.remember']);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['code' => 'That code is invalid.']);
    }

    private function codeIsValid(User $user, string $code): bool
    {
        if ((new Google2FA)->verifyKey($user->two_factor_secret, $code)) {
            return true;
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        if (in_array($code, $recoveryCodes, true)) {
            $user->two_factor_recovery_codes = array_values(array_diff($recoveryCodes, [$code]));
            $user->save();

            return true;
        }

        return false;
    }
}
