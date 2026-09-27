<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactor\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly Totp $totp)
    {
        //
    }

    /**
     * Show the challenge for the user that just entered valid credentials.
     */
    public function create(Request $request): RedirectResponse|Response
    {
        if (Auth::check()) {
            return redirect()->route('profile.edit');
        }

        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    /**
     * Complete the login after a valid one-time or recovery code.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->session()->get('login.id');

        /** @var User|null $user */
        $user = User::query()->find($userId);

        if (! $user instanceof User) {
            $request->session()->forget(['login.id', 'login.remember']);

            return redirect()->route('login');
        }

        $this->ensureIsNotRateLimited($request, $user);

        $code = (string) $request->string('code');
        $secret = (string) $user->two_factor_secret;

        if (! $this->totp->verify($secret, $code) && ! $user->useRecoveryCode($code)) {
            RateLimiter::hit($this->throttleKey($request, $user));

            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        $remember = (bool) $request->session()->get('login.remember');

        Auth::login($user, $remember);

        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('profile.edit', absolute: false));
    }

    /**
     * Guard the challenge against code guessing.
     */
    private function ensureIsNotRateLimited(Request $request, User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request, $user), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request, $user));

        throw ValidationException::withMessages([
            'code' => trans('auth.throttle', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    private function throttleKey(Request $request, User $user): string
    {
        return 'two-factor:'.$user->getKey().'|'.$request->ip();
    }
}
