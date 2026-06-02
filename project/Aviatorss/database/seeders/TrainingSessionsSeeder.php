<?php

namespace Database\Seeders;

use App\Models\LocationTraining;
use App\Models\Sport;
use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrainingSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $sports = Sport::all();
        $locations = LocationTraining::all();
        if ($sports->isEmpty() || $locations->isEmpty()) {
            return;
        }

        $titles = [
            'Тренировка по ОФП',
            'Тактическая подготовка',
            'Техническая тренировка',
            'Сбор команды',
            'Спарринг / товарищеская игра',
            'Разбор ошибок и теория',
            'Командная игра',
        ];
        $descriptions = [
            'Разминка, основная часть, заминка. Не забудьте воду и сменную обувь.',
            'Работа над техникой, упражнения на координацию и выносливость.',
            'Сбор по расписанию. Опоздавших просим предупредить заранее.',
            'Выполняем план тренера. Нагрузка средняя.',
        ];

        // Запланированные/идущие + немного завершенных
        $statuses = ['scheduled', 'scheduled', 'scheduled', 'in_progress', 'completed'];

        for ($i = 0; $i < 20; $i++) {
            $status = $statuses[array_rand($statuses)];

            if ($status === 'completed') {
                $end = Carbon::now()->subDays(rand(1, 20))->setTime(rand(10, 19), 0);
                $start = (clone $end)->subHours(2);
            } elseif ($status === 'in_progress') {
                $start = Carbon::now()->subMinutes(rand(10, 60));
                $end = Carbon::now()->addMinutes(rand(30, 120));
            } else {
                $start = Carbon::now()->addDays(rand(1, 20))->setTime(rand(9, 19), 0);
                $end = (clone $start)->addHours(2);
            }

            TrainingSession::factory()->create([
                'sport_id' => $sports->random()->id,
                'locations_id' => $locations->random()->id,
                'status' => $status,
                'title' => $titles[array_rand($titles)],
                'description' => $descriptions[array_rand($descriptions)],
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }
    }
}

