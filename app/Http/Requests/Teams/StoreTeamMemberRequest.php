<?php

declare(strict_types=1);

namespace App\Http\Requests\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTeamMemberRequest extends FormRequest
{
    /**
     * Only the owner may invite new members.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        /** @var Team|null $team */
        $team = $this->route('team');

        return $user instanceof User && $team instanceof Team && $user->ownsTeam($team);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        /** @var Team $team */
        $team = $this->route('team');

        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('team_invitations')->where(
                    fn (Builder $query): Builder => $query->where('team_id', (int) $team->getKey()),
                ),
            ],
        ];
    }
}
