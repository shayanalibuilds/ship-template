<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version for cache busting.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default across every page.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
            ],
            'auth' => [
                'user' => fn (): ?array => $request->user()?->only(
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                ),
            ],
            'features' => fn (): array => [
                'email_verification' => (bool) config('features.email_verification'),
                'two_factor' => (bool) config('features.two_factor'),
                'teams' => (bool) config('features.teams'),
            ],
            'teams' => fn (): ?array => $this->teams($request),
            'status' => fn (): ?string => $request->session()->get('status'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }

    /**
     * The current team switcher payload, only while the teams feature is on.
     *
     * @return array{current: array{id: int, name: string}|null, all: array<int, array{id: int, name: string}>}|null
     */
    private function teams(Request $request): ?array
    {
        if (! (bool) config('features.teams')) {
            return null;
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        return [
            'current' => $user->currentTeam?->only('id', 'name'),
            'all' => $user->allTeams()
                ->map(fn (Team $team): array => [
                    'id' => (int) $team->getKey(),
                    'name' => $team->name,
                ])
                ->values()
                ->all(),
        ];
    }
}
