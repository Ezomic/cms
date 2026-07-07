<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.two-factor.show', [
            'user' => $request->user(),
            'otpAuthUri' => $this->buildOtpAuthUri($request->user()),
            'recoveryCodes' => session('two_factor_recovery_codes'),
        ]);
    }

    public function enable(Request $request)
    {
        $user = $request->user();
        $user->two_factor_secret = (new Google2FA)->generateSecretKey();
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return redirect()->route('admin.two-factor.show');
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->two_factor_secret || ! (new Google2FA)->verifyKey($user->two_factor_secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'That code is invalid.']);
        }

        $codes = collect(range(1, 8))->map(fn () => Str::random(10))->all();

        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = $codes;
        $user->save();

        return redirect()->route('admin.two-factor.show')
            ->with('two_factor_recovery_codes', $codes)
            ->with('status', 'Two-factor authentication enabled. Save your recovery codes somewhere safe.');
    }

    public function disable(Request $request)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->route('admin.two-factor.show')->with('status', 'Two-factor authentication disabled.');
    }

    private function buildOtpAuthUri($user): ?string
    {
        if (! $user->two_factor_secret) {
            return null;
        }

        return (new Google2FA)->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret,
        );
    }
}
