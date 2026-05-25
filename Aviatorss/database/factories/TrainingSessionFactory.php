<?php

namespace Database\Factories;

use App\Models\LocationTraining;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    protected $model = TrainingSession::class;

    public function definition(): array
    {
        $startTime = Carbon::now()->addDays(rand(1, 30))->setTime(rand(9, 18), rand(0, 59));
        $endTime = (clone $startTime)->addHours(2);

        return [
            'sport_id' => Sport::factory(),
            'team_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled',
            'locations_id' => LocationTraining::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (TrainingSession $session) {
            if ($session->team_id) {
                return;
            }
            $team = Team::query()->where('sport_id', $session->sport_id)->first();
            if (! $team) {
                $team = Team::factory()->create(['sport_id' => $session->sport_id]);
            }
            $session->forceFill(['team_id' => $team->id])->saveQuietly();
        });
    }
}

