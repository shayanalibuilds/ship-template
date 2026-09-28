<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia;

test('the teams routes are not found while the feature is off', function (): void {
    config(['features.teams' => false]);

    $user = User::factory()->create();

    $this->actingAs($user)->get(route('teams.index'))->assertNotFound();

    $this->actingAs($user)
        ->post(route('teams.store'), ['name' => 'Hidden'])
        ->assertNotFound();
});

test('a user can create a team', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('teams.store'), [
        'name' => 'Acme Rockets',
    ]);

    $team = Team::query()->where('name', 'Acme Rockets')->firstOrFail();

    $response->assertRedirect(route('teams.show', $team));

    expect($team->user_id)->toBe($user->getKey())
        ->and($user->fresh()->ownsTeam($team))->toBeTrue();
});

test('the teams index lists owned, joined teams and outstanding invitations', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $owned = Team::factory()->for($owner, 'owner')->create(['name' => 'Owned Team']);
    $joined = Team::factory()->create(['name' => 'Joined Team']);
    $joined->users()->attach($member);

    TeamInvitation::query()->create([
        'team_id' => $owned->getKey(),
        'email' => $member->email,
    ]);

    $response = $this->actingAs($member)->get(route('teams.index'));

    $response->assertInertia(
        fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Teams/Index')
            ->has('teams.all', 1)
            ->where('teams.all.0.name', 'Joined Team')
            ->has('invitations', 1)
            ->where('invitations.0.team', 'Owned Team')
            ->whereType('invitations.0.acceptUrl', 'string'),
    );
});

test('owners and members can view a team while strangers cannot', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $stranger = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $this->actingAs($owner)->get(route('teams.show', $team))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Teams/Show')
                ->where('team.isOwner', true),
        );

    $this->actingAs($member)->get(route('teams.show', $team))->assertOk();

    $this->actingAs($stranger)->get(route('teams.show', $team))->assertForbidden();
});

test('only the owner can rename the team', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $this->actingAs($member)
        ->from(route('teams.show', $team))
        ->patch(route('teams.update', $team), ['name' => 'Hijacked'])
        ->assertForbidden();

    $this->actingAs($owner)
        ->from(route('teams.show', $team))
        ->patch(route('teams.update', $team), ['name' => 'Renamed Team'])
        ->assertRedirect();

    expect($team->fresh()->name)->toBe('Renamed Team');
});

test('only the owner can delete the team and memberships are reset', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $member->current_team_id = $team->getKey();
    $member->save();

    $this->actingAs($member)
        ->delete(route('teams.destroy', $team))
        ->assertForbidden();

    $this->actingAs($owner)
        ->delete(route('teams.destroy', $team))
        ->assertRedirect(route('teams.index'));

    expect(Team::query()->find($team->getKey()))->toBeNull()
        ->and($member->fresh()->current_team_id)->toBeNull();
});

test('owners can invite by email once per team', function (): void {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)
        ->from(route('teams.show', $team))
        ->post(route('teams.invitations.store', $team), ['email' => 'new@example.com'])
        ->assertRedirect();

    Notification::assertSentOnDemand(TeamInvitationNotification::class);

    expect($team->invitations()->where('email', 'new@example.com')->count())->toBe(1);

    $this->actingAs($owner)
        ->from(route('teams.show', $team))
        ->post(route('teams.invitations.store', $team), ['email' => 'new@example.com'])
        ->assertSessionHasErrors('email');
});

test('inviting an existing member fails', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $this->actingAs($owner)
        ->from(route('teams.show', $team))
        ->post(route('teams.invitations.store', $team), ['email' => $member->email])
        ->assertSessionHasErrors('email');
});

test('members cannot invite', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $this->actingAs($member)
        ->post(route('teams.invitations.store', $team), ['email' => 'somebody@example.com'])
        ->assertForbidden();
});

test('invitations are accepted through the signed link', function (): void {
    $owner = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'invite@example.com']);

    $team = Team::factory()->for($owner, 'owner')->create();

    $invitation = $team->invitations()->create([
        'email' => 'invite@example.com',
    ]);

    $url = URL::signedRoute('teams.invitations.accept', ['invitation' => $invitation->getKey()]);

    $response = $this->actingAs($invitee)->get($url);

    $response->assertRedirect(route('teams.show', $team));

    expect($invitee->fresh()->belongsToTeam($team))->toBeTrue()
        ->and(TeamInvitation::query()->find($invitation->getKey()))->toBeNull();
});

test('the invitation link refuses a different account', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();

    $invitation = $team->invitations()->create([
        'email' => 'invite@example.com',
    ]);

    $url = URL::signedRoute('teams.invitations.accept', ['invitation' => $invitation->getKey()]);

    $this->actingAs($stranger)->get($url)->assertForbidden();

    expect($stranger->fresh()->belongsToTeam($team))->toBeFalse()
        ->and(TeamInvitation::query()->find($invitation->getKey()))->not->toBeNull();
});

test('owners can cancel invitations and members cannot', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $invitation = $team->invitations()->create([
        'email' => 'invite@example.com',
    ]);

    $this->actingAs($member)
        ->delete(route('teams.invitations.destroy', ['team' => $team, 'invitation' => $invitation]))
        ->assertForbidden();

    $this->actingAs($owner)
        ->delete(route('teams.invitations.destroy', ['team' => $team, 'invitation' => $invitation]))
        ->assertRedirect();

    expect(TeamInvitation::query()->find($invitation->getKey()))->toBeNull();
});

test('owners can remove members and their current team resets', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $member->current_team_id = $team->getKey();
    $member->save();

    $this->actingAs($owner)
        ->delete(route('teams.members.destroy', ['team' => $team, 'user' => $member]))
        ->assertRedirect();

    expect($team->fresh()->users()->count())->toBe(0)
        ->and($member->fresh()->current_team_id)->toBeNull();
});

test('users can only switch to teams they can reach', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $stranger = User::factory()->create();

    $team = Team::factory()->for($owner, 'owner')->create();
    $team->users()->attach($member);

    $elsewhere = Team::factory()->for($stranger, 'owner')->create();

    $this->actingAs($member)
        ->post(route('teams.switch', $team))
        ->assertRedirect();

    expect($member->fresh()->current_team_id)->toBe($team->getKey());

    $this->actingAs($member)
        ->post(route('teams.switch', $elsewhere))
        ->assertForbidden();

    $this->actingAs($stranger)
        ->post(route('teams.switch', $team))
        ->assertForbidden();
});

test('the shared teams payload follows the current team and the feature flag', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create(['name' => 'Payload Team']);

    $owner->current_team_id = $team->getKey();
    $owner->save();

    $this->actingAs($owner)->get(route('teams.index'))
        ->assertInertia(
            fn (AssertableInertia $page): AssertableInertia => $page
                ->where('teams.current.id', $team->getKey())
                ->where('teams.current.name', 'Payload Team')
                ->has('teams.all', 1),
        );

    config(['features.teams' => false]);

    $this->actingAs($owner)->get(route('teams.index'))
        ->assertNotFound();
});
