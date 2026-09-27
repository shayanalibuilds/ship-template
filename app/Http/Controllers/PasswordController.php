<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Auth\UpdateUserPassword;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request, UpdateUserPassword $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $action->handle($user, $request->validated());

        return back()->with('status', 'password-updated');
    }
}
