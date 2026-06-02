<?php

namespace Database\Seeders;

use App\Models\ApplicationCompetition;
use App\Models\Competition;
use Database\Seeders\Concerns\UsesSeedUsers;
use Illuminate\Database\Seeder;

class ApplicationCompetitionSeeder extends Seeder
{
    use UsesSeedUsers;

    public function run(): void
    {
        $students = $this->seedStudents();
        $teacher = $this->seedTeacher();

        if ($students->isEmpty()) {
            return;
        }

        Competition::query()
            ->where('status', 'upcoming')
            ->limit(8)
            ->each(function (Competition $competition) use ($students, $teacher) {
                $student = $students->random();
                ApplicationCompetition::query()->firstOrCreate(
                    [
                        'user_id' => $student->id,
                        'competition_id' => $competition->id,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );

                if ($teacher) {
                    $acceptedStudent = $students->where('id', '!=', $student->id)->random();
                    ApplicationCompetition::query()->firstOrCreate(
                        [
                            'user_id' => $acceptedStudent->id,
                            'competition_id' => $competition->id,
                        ],
                        [
                            'status' => 'accepted',
                            'accepted_by_user_id' => $teacher->id,
                            'accepted_at' => now()->subDays(rand(1, 5)),
                        ]
                    );
                }
            });
    }
}
