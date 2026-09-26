<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReleaseStatus;
use Database\Factories\ReleaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Release extends Model
{
    /** @use HasFactory<ReleaseFactory> */
    use HasFactory;

    protected $fillable = ['title', 'slug', 'body', 'status'];

    protected function casts(): array
    {
        return [
            'status' => ReleaseStatus::class,
        ];
    }

    /**
     * Scope a query to only include published releases.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ReleaseStatus::Published);
    }
}
