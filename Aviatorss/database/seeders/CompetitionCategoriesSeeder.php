<?php

namespace Database\Seeders;

use App\Models\CompetitionCategory;
use Illuminate\Database\Seeder;

class CompetitionCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name_category' => 'Городские', 'description' => 'Соревнования городского уровня'],
            ['name_category' => 'Областные', 'description' => 'Соревнования областного уровня'],
            ['name_category' => 'Всероссийские', 'description' => 'Соревнования всероссийского уровня'],
            ['name_category' => 'Товарищеские', 'description' => 'Неформальные/товарищеские мероприятия'],
        ];

        foreach ($defaults as $row) {
            CompetitionCategory::query()->firstOrCreate(
                ['name_category' => $row['name_category']],
                ['description' => $row['description']]
            );
        }
    }
}

