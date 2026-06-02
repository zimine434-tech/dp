<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionParticipant;
use Database\Seeders\Concerns\UsesSeedUsers;
use Illuminate\Database\Seeder;

class CompetitionParticipantsSeeder extends Seeder
{
    use UsesSeedUsers;

    public function run(): void
    {
        $students = $this->seedStudents();
        if ($students->isEmpty()) {
            return;
        }

        $competitions = Competition::query()
            ->whereIn('status', ['upcoming', 'ongoing', 'finished'])
            ->get();

        foreach ($competitions as $competition) {
            $picked = $students->take(min(6, $students->count()));
            foreach ($picked as $student) {
                CompetitionParticipant::query()->firstOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'user_id' => $student->id,
                    ],
                    ['role' => 'student']
                );
            }
        }
    }
}
