<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Auth\DeleteUser;
use App\Actions\Auth\UpdateUserProfile;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateUserProfile $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $action->handle($user, $request->validated());

        return back()->with('status', 'profile-updated');
    }

    public function destroy(DeleteUserRequest $request, DeleteUser $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Auth::guard('web')->logout();

        $action->handle($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
