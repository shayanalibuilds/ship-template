<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\SwitchTeamRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

final class TeamController extends Controller
{
    /**
     * Every team the user owns, belongs to or was invited to.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Teams/Index', [
            'invitations' => TeamInvitation::query()
                ->where('email', $user->email)
                ->with('team')
                ->get()
                ->map(fn (TeamInvitation $invitation): array => $this->invitationPayload($invitation))
                ->all(),
        ]);
    }

    /**
     * Create a team owned by the current user.
     */
    public function store(StoreTeamRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $team = new Team([
            'name' => $request->validated('name'),
        ]);

        $team->user_id = (int) $user->getKey();
        $team->save();

        return redirect()->route('teams.show', $team)->with('success', 'Team created.');
    }

    /**
     * The team management page for owners and members.
     */
    public function show(Request $request, Team $team): Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->belongsToTeam($team), 403);

        return Inertia::render('Teams/Show', [
            'team' => $this->teamPayload($team->load('owner'), $user),
            'members' => collect($team->users)
                ->map(fn (User $member): array => [
                    'id' => (int) $member->getKey(),
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => (string) data_get($member, 'pivot.role', 'member'),
                ])
                ->all(),
            'invitations' => collect($team->invitations)
                ->map(fn (TeamInvitation $invitation): array => $this->invitationPayload($invitation))
                ->all(),
        ]);
    }

    /**
     * Rename a team, owners only.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->update([
            'name' => (string) $request->validated('name'),
        ]);

        return back()->with('success', 'Team renamed.');
    }

    /**
     * Delete a team, owners only. Memberships and invitations go with it.
     */
    public function destroy(Request $request, Team $team): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->ownsTeam($team), 403);

        User::query()->where('current_team_id', $team->getKey())->update([
            'current_team_id' => null,
        ]);

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted.');
    }

    /**
     * Make the given team the user's current one.
     */
    public function switch(SwitchTeamRequest $request, Team $team): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->current_team_id = (int) $team->getKey();
        $user->save();

        return back()->with('success', 'Switched to '.$team->name.'.');
    }

    /**
     * @return array{id: int, name: string, isOwner: bool}
     */
    private function teamPayload(Team $team, User $user): array
    {
        return [
            'id' => (int) $team->getKey(),
            'name' => $team->name,
            'isOwner' => $user->ownsTeam($team),
        ];
    }

    /**
     * @return array{id: int, team: string, acceptUrl: string}
     */
    private function invitationPayload(TeamInvitation $invitation): array
    {
        $teamName = $invitation->team?->name;

        return [
            'id' => (int) $invitation->getKey(),
            'team' => is_string($teamName) ? $teamName : '',
            'acceptUrl' => (string) URL::signedRoute(
                'teams.invitations.accept',
                ['invitation' => (int) $invitation->getKey()],
            ),
        ];
    }
}
