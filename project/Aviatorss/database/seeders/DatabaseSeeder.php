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
            SportsSeeder::class,
            TeamsSeeder::class,
            LocationsSeeder::class,
            CompetitionCategoriesSeeder::class,
            CompetitionsSeeder::class,
            CompetitionParticipantsSeeder::class,
            CompetitionResultsSeeder::class,
            TrainingSessionsSeeder::class,
            TrainingRegistrationsSeeder::class,
            TeamJoinRequestsSeeder::class,
            ApplicationCompetitionSeeder::class,
            ScheduleGroupsSeeder::class,
            NewsSeeder::class,
        ]);
    }
}
