<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $two_factor_secret
 * @property list<string>|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property int|null $current_team_id
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Whether the user finished the two-factor setup and is challenged on login.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return is_string($this->two_factor_secret)
            && $this->two_factor_secret !== ''
            && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Whether a secret is stored but the setup is not confirmed yet.
     */
    public function hasPendingTwoFactor(): bool
    {
        return is_string($this->two_factor_secret)
            && $this->two_factor_secret !== ''
            && $this->two_factor_confirmed_at === null;
    }

    /**
     * The decrypted recovery codes, used ones included as they are stored.
     *
     * @return list<string>
     */
    public function recoveryCodes(): array
    {
        $codes = $this->two_factor_recovery_codes;

        if (! is_array($codes)) {
            return [];
        }

        /** @var list<string> $codes */
        return $codes;
    }

    /**
     * Replace the recovery codes with a fresh batch and return it.
     *
     * @return list<string>
     */
    public function generateRecoveryCodes(): array
    {
        /** @var list<string> $codes */
        $codes = Collection::times(10, fn (): string => $this->recoveryCode())->all();

        $this->two_factor_recovery_codes = $codes;
        $this->save();

        return $codes;
    }

    /**
     * Consume one recovery code, reporting whether it was valid.
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->recoveryCodes();

        $index = array_search($code, $codes, true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);

        $this->two_factor_recovery_codes = array_values($codes);
        $this->save();

        return true;
    }

    /**
     * The teams the user owns.
     *
     * @return HasMany<Team, $this>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    /**
     * The teams the user belongs to, owned ones included.
     *
     * @return BelongsToMany<Team, $this, Pivot>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
    }

    /**
     * The team the user is currently working in.
     *
     * @return BelongsTo<Team, $this>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Every team the user can reach: owned plus joined, made unique.
     *
     * @return Collection<int, Team>
     */
    public function allTeams(): Collection
    {
        return $this->ownedTeams->merge($this->teams)->unique('id')->values();
    }

    /**
     * Whether the user owns the team.
     */
    public function ownsTeam(Team $team): bool
    {
        return $team->user_id === $this->getKey();
    }

    /**
     * Whether the user is the owner of or a member in the team.
     */
    public function belongsToTeam(Team $team): bool
    {
        return $this->ownsTeam($team) || $this->teams->contains('id', $team->getKey());
    }

    /**
     * Send the verification email only while the feature is switched on.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (! (bool) Config::boolean('features.email_verification')) {
            return;
        }

        $this->notify(new VerifyEmail);
    }

    /**
     * A single eight character recovery code chunk.
     */
    private function recoveryCode(): string
    {
        return strtoupper(Str::random(5)).'-'.strtoupper(Str::random(5));
    }
}
