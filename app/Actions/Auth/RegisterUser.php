<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

final class RegisterUser
{
    /**
     * @param  array{name: string, email: string, password: string}  $input
     */
    public function handle(array $input): User
    {
        $user = User::create($input);

        event(new Registered($user));

        return $user;
    }
}
