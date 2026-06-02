<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Отчет по результатам</title>
    <style>
        @page { margin: 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 6px 0; }
        .meta { font-size: 11px; color: #374151; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 6px 6px; vertical-align: top; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #374151; }
        .w-teacher { width: 20%; }
        .w-result { width: 13%; }
        .w-date { width: 13%; }
        .small { font-size: 10px; color: #4b5563; }
        .muted { color: #6b7280; }
        .pre { white-space: pre-line; }
    </style>
</head>
<body>
    <h1>Участие студентов в спортивных и массовых мероприятиях</h1>
    <div class="meta">
        Период: {{ $monthLabel }} ({{ $monthStart->format('d.m.Y') }} – {{ $monthEnd->format('d.m.Y') }})
    </div>

    <table>
        <thead>
        <tr>
            <th class="w-teacher">Руководитель</th>
            <th>Мероприятие</th>
            <th>Участник</th>
            <th class="w-result">Результат</th>
            <th class="w-date">Дата</th>
        </tr>
        </thead>
        <tbody>
        @forelse($competitions as $competition)
            @php
                // Руководитель берётся из ответственного преподавателя соревнования.
                // Если по старым данным он не задан, берём первого преподавателя из участников.
                $teacherUser = $competition->teacher?->user;
                if (! $teacherUser) {
                    $teacherUser = ($competition->participants ?? collect())
                        ->map(fn ($p) => $p->user)
                        ->filter(fn ($u) => $u && $u->role === 'teacher')
                        ->first();
                }
                $teacherLabel = $teacherUser
                    ? trim($teacherUser->lastname.' '.$teacherUser->firstname.' '.($teacherUser->patronymic ?? ''))
                    : '—';

                $dateLabel = $competition->start_date && $competition->end_date
                    ? ($competition->start_date->format('d.m.Y') === $competition->end_date->format('d.m.Y')
                        ? $competition->start_date->format('d.m.Y')
                        : ($competition->start_date->format('d.m.Y').'–'.$competition->end_date->format('d.m.Y')))
                    : '—';

                $participants = ($competition->participants ?? collect())
                    ->filter(fn ($p) => $p->user && $p->user->role === 'student')
                    ->map(fn ($p) => $p->user)
                    ->unique('id')
                    ->values();

                $participantsLabels = $participants->map(function ($u) {
                    $fio = trim(
                        $u->lastname.' '.
                        mb_substr($u->firstname ?? '', 0, 1).'.'.
                        (filled($u->patronymic) ? (' '.mb_substr($u->patronymic, 0, 1).'.') : '')
                    );
                    $group = filled($u->group_name) ? (' ('.$u->group_name.')') : '';
                    return trim($fio).$group;
                });

                $resultPlaces = ($competition->results ?? collect())
                    ->pluck('place')
                    ->map(fn ($p) => trim((string) $p))
                    ->filter()
                    ->unique()
                    ->filter(function (string $place) {
                        if (! is_numeric($place)) {
                            return false;
                        }
                        $n = (int) $place;
                        return $n >= 1 && $n <= 3;
                    })
                    ->values();

                $resultLabel = $resultPlaces->count() > 0
                    ? $resultPlaces->map(fn ($p) => $p.' место')->join(', ')
                    : '—';
            @endphp
            <tr>
                <td>{{ $teacherLabel }}</td>
                <td>
                    <div>{{ $competition->name }}</div>
                </td>
                <td class="pre">
                    @if($participantsLabels->count() > 0)
                        {{ $participantsLabels->join("\n") }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ $resultLabel }}</td>
                <td>{{ $dateLabel }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; padding: 16px;" class="muted">
                    За выбранный месяц нет завершённых соревнований.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>

