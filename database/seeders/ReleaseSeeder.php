<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Release;
use Illuminate\Database\Seeder;

final class ReleaseSeeder extends Seeder
{
    public function run(): void
    {
        Release::factory()->published()->count(5)->create();
        Release::factory()->create();
    }
}
