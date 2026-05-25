<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    public function run(): void
    {
        $teamNames = [
            'Авиатор',
            'Сокол',
            'Орлы Сибири',
            'Северный Ветер',
            'Иркутские Медведи',
            'Байкальские Акулы',
            'Стрижи',
            'Виктория',
        ];
        $teamDescriptions = [
            'Команда спортивного клуба. Тренировки проходят регулярно по расписанию.',
            'Основной состав клуба. Участие в городских и областных соревнованиях.',
            'Молодёжный состав. Приоритет — развитие техники и командной игры.',
            'Сборная факультета. Подготовка к турнирам и выездным мероприятиям.',
        ];

        $teams = collect($teamNames)->map(function (string $name) use ($teamDescriptions) {
            return Team::query()->create([
                'name' => $name,
                'description' => $teamDescriptions[array_rand($teamDescriptions)],
            ]);
        });

        $teachers = User::query()->where('role', 'teacher')->get();
        $students = User::query()->where('role', 'student')->get();

        if ($teachers->isEmpty() || $students->isEmpty()) {
            return;
        }

        foreach ($teams as $team) {
            // 1 тренер
            $coach = $teachers->random();
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $coach->id,
                'id_adding' => $coach->id,
                'joined_at' => now()->subDays(rand(10, 200)),
                'type_user' => 'coach',
            ]);

            // Капитан + участники
            $teamStudents = $students->random(min(10, $students->count()))->shuffle();
            foreach ($teamStudents as $idx => $student) {
                TeamMember::create([
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

