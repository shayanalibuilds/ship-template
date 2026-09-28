<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactor\QrSvg;
use App\Services\TwoFactor\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class TwoFactorController extends Controller
{
    public function __construct(private readonly Totp $totp, private readonly QrSvg $qrSvg)
    {
        //
    }

    /**
     * The two-factor section of the security page.
     */
    public function show(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $secret = $user->hasPendingTwoFactor() ? (string) $user->two_factor_secret : null;

        return Inertia::render('Security/Index', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirming' => $user->hasPendingTwoFactor(),
            'secret' => $secret,
            'qrSvg' => $secret === null ? null : $this->qrSvg->svg(
                $this->totp->otpauthUri((string) config('app.name'), $user->email, $secret),
            ),
            'recoveryCodes' => fn (): ?array => $request->session()->get('two-factor-codes'),
        ]);
    }

    /**
     * Start the setup by storing a fresh, unconfirmed secret.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return back();
        }

        $user->two_factor_secret = $this->totp->generateSecret();
        $user->two_factor_confirmed_at = null;
        $user->save();

        return back();
    }

    /**
     * Confirm the setup with a code from the user's authenticator.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! $user->hasPendingTwoFactor()) {
            return back();
        }

        $secret = (string) $user->two_factor_secret;

        if (! $this->totp->verify($secret, (string) $request->string('code'))) {
            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        $codes = $user->generateRecoveryCodes();

        return back()
            ->with('two-factor-codes', $codes)
            ->with('success', 'Two-factor authentication is now enabled. Store the recovery codes somewhere safe.');
    }

    /**
     * Hand out a fresh batch of recovery codes.
     */
    public function regenerate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return back();
        }

        return back()->with('two-factor-codes', $user->generateRecoveryCodes());
    }

    /**
     * Switch two-factor authentication off again.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }
}
