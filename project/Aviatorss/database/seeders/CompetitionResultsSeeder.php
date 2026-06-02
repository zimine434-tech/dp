<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionResult;
use App\Models\Team;
use Illuminate\Database\Seeder;

class CompetitionResultsSeeder extends Seeder
{
    public function run(): void
    {
        $places = ['1', '2', '3'];

        Competition::query()
            ->where('status', 'finished')
            ->each(function (Competition $competition) use ($places) {
                $teams = Team::query()
                    ->where('sport_id', $competition->sport_id)
                    ->limit(3)
                    ->get();

                foreach ($teams as $index => $team) {
                    CompetitionResult::query()->firstOrCreate(
                        [
                            'competitions_id' => $competition->id,
                            'teams_id' => $team->id,
                        ],
                        [
                            'place' => $places[$index] ?? (string) ($index + 1),
                            'is_archive' => false,
                        ]
                    );
                }
            });
    }
}
