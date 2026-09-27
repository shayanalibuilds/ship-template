<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teams\TeamController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\Teams\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('feature:two_factor')->group(function (): void {
    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
        ->name('two-factor.challenge');
    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->name('two-factor.challenge.store');
});

Route::middleware('auth')->group(function (): void {
    Route::middleware('feature:email_verification')->group(function (): void {
        Route::get('verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::middleware('verified')->group(function (): void {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::middleware(['feature:two_factor', 'password.confirm'])->group(function (): void {
            Route::get('security', [TwoFactorController::class, 'show'])->name('security.show');
            Route::post('security/two-factor', [TwoFactorController::class, 'store'])->name('two-factor.store');
            Route::post('security/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
            Route::post('security/two-factor/recovery-codes', [TwoFactorController::class, 'regenerate'])->name('two-factor.recovery-codes');
            Route::delete('security/two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');
        });

        Route::middleware('feature:teams')->group(function (): void {
            Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
            Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
            Route::get('teams/{team}', [TeamController::class, 'show'])->name('teams.show');
            Route::patch('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
            Route::delete('teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
            Route::post('teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');

            Route::delete('teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');

            Route::post('teams/{team}/invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');
            Route::delete('teams/{team}/invitations/{invitation}', [TeamInvitationController::class, 'destroy'])->name('teams.invitations.destroy');
            Route::get('teams/invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])
                ->middleware('signed')
                ->name('teams.invitations.accept');
        });
    });
});
