<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::query()->where('role', 'teacher')->get();
        if ($teachers->isEmpty()) {
            return;
        }

        $titles = [
            'Прошедшие соревнования: итоги недели',
            'Ближайшие соревнования: расписание и регламент',
            'Тренировки на этой неделе: обновлённое расписание',
            'Результаты соревнований: опубликованы места',
            'Список участников: изменения в составе',
            'Открыт приём заявок на участие',
            'Напоминание участникам: документы и форма',
            'Изменение места проведения мероприятия',
        ];
        $paragraphs = [
            'Публикуем краткие итоги и основные результаты. Полный протокол доступен в разделе «Результаты соревнований».',
            'Просим участников проверить дату, время и место проведения, а также требования к экипировке.',
            'Приём заявок открыт. Заявку можно подать в карточке соревнования во вкладке «Соревнования».',
            'В случае изменений мы обновим информацию на сайте и в ленте новостей.',
            'По организационным вопросам обращайтесь к ответственному преподавателю.',
            'Просьба прибыть за 15–20 минут до начала для регистрации и разминки.',
        ];

        for ($i = 0; $i < 18; $i++) {
            $date = Carbon::now()->subDays(rand(0, 90))->toDateString();
            $shuffled = collect($paragraphs)->shuffle()->values()->all();
            News::create([
                'name' => $titles[array_rand($titles)],
                'description' => implode("\n\n", array_slice($shuffled, 0, rand(2, 4))),
                'created_by' => $teachers->random()->id,
                'date' => $date,
                'status' => $i < 12 ? 'Published' : 'Draft',
            ]);
        }
    }
}

