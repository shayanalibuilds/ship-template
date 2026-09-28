<?php

declare(strict_types=1);

namespace App\Http\Requests\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class SwitchTeamRequest extends FormRequest
{
    /**
     * The user may only switch to a team they can reach.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        /** @var Team|null $team */
        $team = $this->route('team');

        return $user instanceof User && $team instanceof Team && $user->belongsToTeam($team);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
