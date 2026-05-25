<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class SportsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();
        $teachers = User::query()->where('role', 'teacher')->get();
        if ($teams->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $sportNames = [
            'Футзал',
            'Мини‑футбол',
            'Волейбол',
            'Баскетбол',
            'Настольный теннис',
            'Лёгкая атлетика',
            'Плавание',
            'Шахматы',
            'Киберспорт',
            'Хоккей на траве',
        ];
        $sportDescriptions = [
            'Тренировки для начинающих и основного состава.',
            'Подготовка к турнирам и товарищеским матчам.',
            'Развитие выносливости, техники и командного взаимодействия.',
            'Комплексная подготовка: ОФП, техника, тактика.',
        ];

        foreach ($teams as $team) {
            if ($team->sport_id) {
                continue;
            }
            $sport = Sport::query()->create([
                'name' => $sportNames[array_rand($sportNames)],
                'description' => $sportDescriptions[array_rand($sportDescriptions)],
                'created_by' => $teachers->random()->id,
            ]);
            $team->update(['sport_id' => $sport->id]);
        }
    }
}
