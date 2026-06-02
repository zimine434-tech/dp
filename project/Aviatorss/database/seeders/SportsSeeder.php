<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\Team;
use Database\Seeders\Concerns\UsesSeedUsers;
use Illuminate\Database\Seeder;

class SportsSeeder extends Seeder
{
    use UsesSeedUsers;

    public function run(): void
    {
        $teacher = $this->seedTeacher();
        if (! $teacher) {
            return;
        }

        $pairs = [
            ['sport' => 'Баскетбол', 'team' => 'Команда по баскетболу'],
            ['sport' => 'Волейбол', 'team' => 'Команда по волейболу'],
            ['sport' => 'Плавание', 'team' => 'Команда по плаванию'],
            ['sport' => 'Триатлон', 'team' => 'Команда по триатлону'],
            ['sport' => 'Футбол', 'team' => 'Команда по футболу'],
            ['sport' => 'Настольный теннис', 'team' => 'Команда по настольному теннису'],
            ['sport' => 'Лёгкая атлетика', 'team' => 'Команда по лёгкой атлетике'],
            ['sport' => 'Хоккей', 'team' => 'Команда по хоккею'],
            ['sport' => 'Бадминтон', 'team' => 'Команда по бадминтону'],
            ['sport' => 'Дзюдо', 'team' => 'Команда по дзюдо'],
        ];

        $description = 'Секция спортивного клуба. Тренировки по расписанию.';

        foreach ($pairs as $row) {
            $sport = Sport::query()->firstOrCreate(
                ['name' => $row['sport']],
                [
                    'description' => $description,
                    'created_by' => $teacher->id,
                ]
            );

            Team::query()->firstOrCreate(
                ['name' => $row['team']],
                [
                    'description' => 'Основной состав по виду спорта «'.$row['sport'].'».',
                    'sport_id' => $sport->id,
                ]
            );
        }
    }
}
