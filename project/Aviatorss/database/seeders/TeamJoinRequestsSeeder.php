<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamJoinRequest;
use Database\Seeders\Concerns\UsesSeedUsers;
use Illuminate\Database\Seeder;

class TeamJoinRequestsSeeder extends Seeder
{
    use UsesSeedUsers;

    public function run(): void
    {
        $students = $this->seedStudents();
        $teams = Team::query()->get();

        if ($students->isEmpty() || $teams->isEmpty()) {
            return;
        }

        foreach ($teams->random(min(4, $teams->count())) as $team) {
            $student = $students->random();
            TeamJoinRequest::query()->firstOrCreate(
                [
                    'team_id' => $team->id,
                    'user_id' => $student->id,
                    'status' => 'pending',
                ],
                ['message' => 'Хочу вступить в команду.']
            );
        }
    }
}
