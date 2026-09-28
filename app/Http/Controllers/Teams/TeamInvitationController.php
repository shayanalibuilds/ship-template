<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamMemberRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

final class TeamInvitationController extends Controller
{
    /**
     * Invite somebody by email, owners only.
     */
    public function store(StoreTeamMemberRequest $request, Team $team): RedirectResponse
    {
        $request->user();

        $email = (string) $request->validated('email');

        $member = User::query()->where('email', $email)->first();

        if ($member instanceof User && $member->belongsToTeam($team)) {
            return back()->withErrors([
                'email' => 'This user is already part of the team.',
            ]);
        }

        $invitation = $team->invitations()->create([
            'email' => $email,
        ]);

        Notification::route('mail', $email)->notify(new TeamInvitationNotification($invitation));

        return back()->with('success', 'The invitation was sent to '.$email.'.');
    }

    /**
     * Cancel an outstanding invitation, owners only.
     */
    public function destroy(Request $request, Team $team, TeamInvitation $invitation): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless($actor->ownsTeam($team), 403);
        abort_unless((int) $invitation->team_id === (int) $team->getKey(), 404);

        $invitation->delete();

        return back()->with('success', 'The invitation was cancelled.');
    }

    /**
     * Accept an invitation through the signed link in the email.
     */
    public function accept(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($user->email !== $invitation->email, 403, 'This invitation was sent to another email address.');

        $team = $invitation->team;

        if ($team instanceof Team) {
            $team->users()->syncWithoutDetaching([
                (int) $user->getKey() => ['role' => 'member'],
            ]);
        }

        $invitation->delete();

        return $team instanceof Team
            ? redirect()->route('teams.show', $team)->with('success', 'Welcome to '.$team->name.'.')
            : redirect()->route('teams.index');
    }
}
