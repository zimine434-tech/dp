<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\LocationTraining;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        $competitionLocations = [
            ['location' => 'г. Иркутск, стадион «Труд»', 'organizer' => 'Спорткомитет г. Иркутска'],
            ['location' => 'г. Иркутск, ДС «Авиатор»', 'organizer' => 'СК «Авиатор»'],
            ['location' => 'г. Ангарск, СК «Ермак»', 'organizer' => 'Администрация г. Ангарска'],
            ['location' => 'г. Шелехов, ФОК «Металлург»', 'organizer' => 'ФОК «Металлург»'],
            ['location' => 'г. Усолье‑Сибирское, стадион «Химик»', 'organizer' => 'Спортшкола'],
            ['location' => 'г. Иркутск, манеж ИРНИТУ', 'organizer' => 'ИРНИТУ'],
            ['location' => 'п. Листвянка, спортбаза', 'organizer' => 'Байкал‑спорт'],
            ['location' => 'г. Иркутск, бассейн «Кристалл»', 'organizer' => 'МБУ «Кристалл»'],
            ['location' => 'г. Иркутск, школа № 25, спортзал', 'organizer' => 'Школа № 25'],
            ['location' => 'г. Иркутск, зал единоборств «Победа»', 'organizer' => 'Центр «Победа»'],
        ];

        foreach ($competitionLocations as $row) {
            Location::query()->firstOrCreate(
                ['location' => $row['location']],
                ['organizer' => $row['organizer'] ?? null]
            );
        }

        $trainingLocations = [
            ['location' => 'ИРНИТУ, спортзал №1', 'description' => 'Основной зал для игровых видов спорта'],
            ['location' => 'ИРНИТУ, спортзал №2', 'description' => 'Зал для ОФП и разминки'],
            ['location' => 'Манеж ИРНИТУ', 'description' => 'Беговые дорожки и разметка'],
            ['location' => 'Стадион «Труд»', 'description' => 'Открытая площадка (по погоде)'],
            ['location' => 'Бассейн «Кристалл»', 'description' => 'Дорожки 25 м'],
            ['location' => 'Актовый зал (разминка)', 'description' => 'Занятия ОФП в помещении'],
            ['location' => 'СК «Авиатор»', 'description' => 'Тренировки по расписанию клуба'],
            ['location' => 'Площадка во дворе корпуса', 'description' => 'Летние тренировки'],
            ['location' => 'Тренажёрный зал', 'description' => 'Силовая подготовка'],
            ['location' => 'Зал настольного тенниса', 'description' => 'Столы и инвентарь на месте'],
        ];

        foreach ($trainingLocations as $row) {
            LocationTraining::query()->firstOrCreate(
                ['location' => $row['location']],
                ['description' => $row['description'] ?? null]
            );
        }
    }
}

