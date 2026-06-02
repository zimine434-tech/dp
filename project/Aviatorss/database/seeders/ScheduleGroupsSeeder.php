<?php

namespace Database\Seeders;

use App\Models\ScheduleGroup;
use Illuminate\Database\Seeder;

class ScheduleGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'ИС-221', 'remote_id' => 100221, 'course' => 2],
            ['name' => 'ИС-222', 'remote_id' => 100222, 'course' => 2],
            ['name' => 'ПИ-231', 'remote_id' => 100231, 'course' => 3],
            ['name' => 'АС-211', 'remote_id' => 100211, 'course' => 1],
        ];

        foreach ($groups as $row) {
            ScheduleGroup::query()->firstOrCreate(
                ['name' => $row['name']],
                [
                    'remote_id' => $row['remote_id'],
                    'course' => $row['course'],
                ]
            );
        }
    }
}
