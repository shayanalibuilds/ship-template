<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TeamMemberController extends Controller
{
    /**
     * Remove a member from the team, owners only.
     */
    public function destroy(Request $request, Team $team, User $user): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless($actor->ownsTeam($team), 403);
        abort_if((int) $user->getKey() === (int) $team->user_id, 403);

        $team->users()->detach($user->getKey());

        if ((int) $user->current_team_id === (int) $team->getKey()) {
            $user->current_team_id = null;
            $user->save();
        }

        return back()->with('success', $user->name.' was removed from the team.');
    }
}
