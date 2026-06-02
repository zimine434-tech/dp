@php
    /** @var \App\Models\Competition $competition */
    /** @var \App\Models\CompetitionResult|null $result */
    $cDates = $competition;
@endphp
<article
    class="flex h-full min-h-0 min-w-0 flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg"
>
    @include('news.partials.news-cover', ['item' => $competition, 'stacked' => true])
    <div class="flex min-h-0 flex-1 flex-col p-5">
        <div class="mb-2 flex items-start justify-between gap-3">
            <h3 class="min-w-0 flex-1 text-lg font-semibold leading-snug text-gray-900">
                <a href="{{ route('competitions.show', array_merge(['competition' => $competition], $competitionShowQuery ?? [])) }}" class="transition hover:text-blue-600">
                    {{ $competition->name }}
                </a>
            </h3>
            @if($result)
                <span
                    class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                        @if(is_numeric($result->place) && (int) $result->place === 1) bg-yellow-100 text-yellow-800
                        @elseif(is_numeric($result->place) && (int) $result->place === 2) bg-gray-200 text-gray-800
                        @elseif(is_numeric($result->place) && (int) $result->place === 3) bg-orange-100 text-orange-800
                        @else bg-blue-100 text-blue-800
                        @endif"
                >
                    Место: {{ $result->place }}
                </span>
            @else
                <span class="inline-flex shrink-0 items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800">
                    Нет мест
                </span>
            @endif
        </div>
        <p class="mb-3 line-clamp-3 min-h-0 flex-1 text-sm text-gray-600">
            @if(filled($competition->description))
                {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($competition->description ?? '')))), 150) }}
            @else
                {{ $competition->sport?->name ?? 'Соревнование' }}
                @if($competition->participants->count() > 0)
                    · участников: {{ $competition->participants->count() }}
                @endif
            @endif
        </p>
        <div class="mb-3 space-y-1 text-xs text-gray-600 sm:text-sm">
            <p>
                <span class="font-medium text-gray-700">Вид спорта:</span>
                {{ $competition->sport?->name ?? '—' }}
            </p>
            <p>
                <span class="font-medium text-gray-700">Категория:</span>
                {{ $competition->category?->name_category ?? '—' }}
            </p>
        </div>
        <div class="mb-4 text-sm text-gray-500">
            @if($cDates->start_date && $cDates->end_date)
                @if($cDates->start_date->format('Y-m-d') === $cDates->end_date->format('Y-m-d'))
                    <span>Дата: {{ $cDates->start_date->format('d.m.Y') }}</span>
                @else
                    <span>Дата: {{ $cDates->start_date->format('d.m.Y') }} - {{ $cDates->end_date->format('d.m.Y') }}</span>
                @endif
            @elseif($cDates->start_date)
                <span>Дата: {{ $cDates->start_date->format('d.m.Y') }}</span>
            @elseif($cDates->end_date)
                <span>Дата: {{ $cDates->end_date->format('d.m.Y') }}</span>
            @else
                <span class="text-gray-400">Дата не указана</span>
            @endif
        </div>
        <div class="mt-auto flex shrink-0 flex-col gap-2">
            @if(!$result && auth()->user()->role === 'teacher')
                <a
                    href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results', 'add_result' => 1]) }}"
                    class="block rounded-lg bg-green-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-green-700"
                >
                    Добавить результат
                </a>
            @endif
            @if(auth()->user()->role === 'teacher')
                <a
                    href="{{ route('competitions.photos', $competition) }}"
                    class="block rounded-lg bg-indigo-50 px-4 py-2 text-center text-sm font-medium text-indigo-700 transition hover:bg-indigo-100"
                >
                    Добавить фотографии
                </a>
            @endif
            <a
                href="{{ route('competitions.show', array_merge(['competition' => $competition], $competitionShowQuery ?? [])) }}"
                class="block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
            >
                Подробнее
            </a>
        </div>
    </div>
</article>
