<?php

declare(strict_types=1);

namespace App\Actions\Releases;

use App\Enums\ReleaseStatus;
use App\Models\Release;
use Illuminate\Support\Str;

final class CreateRelease
{
    /**
     * @param  array{title: string, body: string}  $data
     */
    public function __invoke(array $data): Release
    {
        // V1: every created release starts as a draft. Publishing is a
        // separate action that ships with the auth PR.
        return Release::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'body' => $data['body'],
            'status' => ReleaseStatus::Draft,
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        // The unique index on slug is the backstop for concurrent creates:
        // a race throws a QueryException, never silent corruption.
        $base = Str::slug($title);
        $slug = $base;
        $attempt = 2;

        while (Release::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$attempt;
            $attempt++;
        }

        return $slug;
    }
}
