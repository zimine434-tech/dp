<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamMember;
use Database\Seeders\Concerns\UsesSeedUsers;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    use UsesSeedUsers;

    public function run(): void
    {
        $coach = $this->seedTeacher();
        $students = $this->seedStudents();

        if (! $coach || $students->isEmpty()) {
            return;
        }

        foreach (Team::query()->get() as $team) {
            if ($team->members()->where('type_user', 'coach')->whereNull('out')->exists()) {
                continue;
            }

            TeamMember::query()->create([
                'team_id' => $team->id,
                'user_id' => $coach->id,
                'id_adding' => $coach->id,
                'joined_at' => now()->subDays(rand(10, 200)),
                'type_user' => 'coach',
            ]);

            $teamStudents = $students->take(min(8, $students->count()));
            foreach ($teamStudents->values() as $idx => $student) {
                TeamMember::query()->create([
                    'team_id' => $team->id,
                    'user_id' => $student->id,
                    'id_adding' => $coach->id,
                    'joined_at' => now()->subDays(rand(1, 180)),
                    'type_user' => $idx === 0 ? 'capitan' : 'member',
                ]);
            }
        }
    }
}
