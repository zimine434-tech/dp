<?php

namespace Database\Seeders;

use App\Models\TrainingRegistration;
use App\Models\TrainingSession;
use Database\Seeders\Concerns\UsesSeedUsers;
use Illuminate\Database\Seeder;

class TrainingRegistrationsSeeder extends Seeder
{
    use UsesSeedUsers;

    public function run(): void
    {
        $students = $this->seedStudents();
        if ($students->isEmpty()) {
            return;
        }

        TrainingSession::query()
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->each(function (TrainingSession $session) use ($students) {
                $picked = $students->take(min(5, $students->count()));
                foreach ($picked as $student) {
                    TrainingRegistration::query()->firstOrCreate(
                        [
                            'training_id' => $session->id,
                            'user_id' => $student->id,
                        ],
                        ['registered_at' => now()->subDays(rand(0, 14))]
                    );
                }
            });
    }
}
