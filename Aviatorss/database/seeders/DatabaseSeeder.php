<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TeamsSeeder::class,
            SportsSeeder::class,
            LocationsSeeder::class,
            CompetitionCategoriesSeeder::class,
            CompetitionsSeeder::class,
            TrainingSessionsSeeder::class,
            NewsSeeder::class,
        ]);
    }
}
