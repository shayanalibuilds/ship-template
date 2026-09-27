<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ConfirmablePasswordController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

    public function store(ConfirmPasswordRequest $request): RedirectResponse
    {
        $request->confirm();

        return redirect()->intended(route('profile.edit', absolute: false));
    }
}
