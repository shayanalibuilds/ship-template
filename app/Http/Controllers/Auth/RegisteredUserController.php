<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisteredUserController extends Controller
{
    public function __construct(private readonly RegisterUser $registerUser)
    {
        //
    }

    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $user = $this->registerUser->handle($request->validated());

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('profile.edit', absolute: false));
    }
}
