<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

final class UpdateUserPassword
{
    /**
     * @param  array{current_password: string, password: string}  $input
     */
    public function handle(User $user, array $input): void
    {
        $user->forceFill([
            'password' => $input['password'],
        ])->save();
    }
}
