<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Location;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Competition>
 */
class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->addDays(rand(7, 60));
        $endDate = (clone $startDate)->addDays(rand(1, 5));

        return [
            'sport_id' => Sport::factory(),
            'team_id' => null,
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => 'upcoming',
            'location_id' => Location::factory(),
            'created_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Competition $competition) {
            if ($competition->team_id) {
                return;
            }
            $team = Team::query()->where('sport_id', $competition->sport_id)->first();
            if (! $team) {
                $team = Team::factory()->create(['sport_id' => $competition->sport_id]);
            }
            $competition->forceFill(['team_id' => $team->id])->saveQuietly();
        });
    }
}

