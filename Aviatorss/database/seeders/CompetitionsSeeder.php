<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\Location;
use App\Models\Sport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompetitionsSeeder extends Seeder
{
    public function run(): void
    {
        $sports = Sport::all();
        $locations = Location::all();
        $teachers = User::query()->where('role', 'teacher')->get();
        if ($sports->isEmpty() || $locations->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $categories = CompetitionCategory::all();

        $nameTemplates = [
            'Кубок %s',
            'Первенство %s',
            'Турнир памяти %s',
            'Открытый чемпионат %s',
            '%s: весенний турнир',
            '%s: осенний турнир',
        ];
        $nameSubjects = [
            'Авиатора',
            'Иркутска',
            'студентов',
            'факультетов',
            'Сибири',
            'Байкала',
        ];
        $descTemplates = [
            'Положение о соревновании будет опубликовано отдельно. Регистрация участников открыта.',
            'Товарищеское мероприятие с призами и дипломами. Уточняйте расписание у тренера.',
            'Соревнование проводится по регламенту организатора. Просьба прибыть заранее.',
            'Состав и расписание уточняются. Следите за обновлениями в ленте новостей.',
        ];

        // Несколько предстоящих + несколько завершенных для результатов/ленты
        $make = function (int $count, string $status) use (
            $sports,
            $locations,
            $teachers,
            $categories,
            $nameTemplates,
            $nameSubjects,
            $descTemplates
        ) {
            for ($i = 0; $i < $count; $i++) {
                if ($status === 'finished') {
                    $end = Carbon::now()->subDays(rand(1, 60));
                    $start = (clone $end)->subDays(rand(1, 5));
                } elseif ($status === 'ongoing') {
                    $start = Carbon::now()->subDays(rand(0, 2));
                    $end = Carbon::now()->addDays(rand(0, 3));
                } else {
                    $start = Carbon::now()->addDays(rand(3, 60));
                    $end = (clone $start)->addDays(rand(1, 5));
                }

                Competition::factory()->create([
                    'sport_id' => $sports->random()->id,
                    'location_id' => $locations->random()->id,
                    'created_by' => $teachers->random()->id,
                    'name' => sprintf(
                        $nameTemplates[array_rand($nameTemplates)],
                        $nameSubjects[array_rand($nameSubjects)]
                    ),
                    'description' => $descTemplates[array_rand($descTemplates)],
                    'status' => $status,
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'competition_category_id' => $categories->isNotEmpty() ? $categories->random()->id : null,
                ]);
            }
        };

        $make(10, 'upcoming');
        $make(4, 'ongoing');
        $make(6, 'finished');
    }
}

