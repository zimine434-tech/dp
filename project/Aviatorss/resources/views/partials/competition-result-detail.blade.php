@php
    $isPersonalCompetition = $competition->isPersonalCompetition();
    $resultByUserId = $competition->results->where('result_type', 'personal')->keyBy('user_id');
    $teamResult = $competition->results->where('result_type', 'team')->first();
@endphp

<div class="mx-auto w-full max-w-4xl space-y-6">
    <div class="rounded-lg bg-white p-6 shadow-md">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ $competition->name }}</h1>
                @if($competition->hasVisibleDescription())
                    <div class="mt-3 text-base text-gray-600 sm:text-lg">
                        @include('partials.rich-text', ['html' => $competition->description, 'class' => 'text-gray-600'])
                    </div>
                @elseif($competition->headerSubtitleText())
                    <p class="mt-3 text-base text-gray-600 sm:text-lg">{{ $competition->headerSubtitleText() }}</p>
                @endif
            </div>
            <a
                href="{{ $backUrl }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 sm:text-base"
            >
                {{ $backLabel }}
            </a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-md">
        <h2 class="mb-4 text-xl font-semibold text-gray-800">Основная информация</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @if(! $isPersonalCompetition)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-500">Вид спорта</label>
                    <p class="text-lg text-gray-900">{{ $competition->sport?->name ?? $competition->team?->sport?->name ?? '—' }}</p>
                </div>
            @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-500">Вид участия</label>
                <p class="text-lg text-gray-900">{{ $competition->resultFormatLabel() }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-500">Категория</label>
                <p class="text-lg text-gray-900">{{ $competition->category?->name_category ?? '—' }}</p>
            </div>

            @if($competition->start_date)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-500">Дата начала</label>
                    <p class="text-lg text-gray-900">{{ $competition->start_date->format('d.m.Y') }}</p>
                </div>
            @endif

            @if($competition->end_date)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-500">Дата окончания</label>
                    <p class="text-lg text-gray-900">{{ $competition->end_date->format('d.m.Y') }}</p>
                </div>
            @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-500">Локация</label>
                <p class="text-lg text-gray-900">{{ $competition->location->location ?? 'Не указана' }}</p>
                @if($competition->location && $competition->location->organizer)
                    <p class="text-sm text-gray-500">Организатор: {{ $competition->location->organizer }}</p>
                @endif
            </div>

            @if(! $isPersonalCompetition)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-500">Место команды</label>
                    <div class="mt-1">
                        @if($teamResult)
                            @include('partials.competition-place-badge', ['place' => $teamResult->place])
                        @else
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                Нет места
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @php
        $competitionPhotos = $competition->images->sortBy('id')->values();
    @endphp
    @if($competitionPhotos->isNotEmpty())
        <div class="overflow-hidden rounded-lg bg-white shadow-md">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-800">Фотографии</h2>
            </div>
            <div class="flex h-[min(28rem,55vh)] min-h-[14rem] w-full flex-col overflow-hidden bg-gray-50">
                @include('news.partials.news-images-carousel', [
                    'images' => $competitionPhotos,
                    'altTitle' => $competition->name,
                    'description' => strip_tags($competition->description ?? ''),
                    'fillCover' => true,
                ])
            </div>
        </div>
        @include('news.partials.news-lightbox')
    @endif

    <div class="rounded-lg bg-white p-6 shadow-md">
        @php
            $count = $competition->participants->count();
        @endphp
        <h2 class="mb-4 text-xl font-semibold text-gray-800">Участники@if($count > 0) ({{ $count }})@endif</h2>

        @if($count > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Фамилия</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Имя</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Роль</th>
                            @if($isPersonalCompetition)
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Вид спорта</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Место</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($competition->participants as $participant)
                            @php
                                $role = $participant->role ?? 'student';
                                $roleNames = [
                                    'student' => 'Участник',
                                    'teacher' => 'Преподаватель',
                                ];
                                $roleName = $roleNames[$role] ?? 'Участник';
                                $personalResult = $resultByUserId->get((int) $participant->user_id);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $participant->user->lastname ?? '—' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $participant->user->firstname ?? '—' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                        {{ $roleName }}
                                    </span>
                                </td>
                                @if($isPersonalCompetition)
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $participant->team?->sport?->name ?? '—' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @include('partials.competition-place-badge', ['place' => $personalResult?->place])
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">Пока нет участников в этом соревновании.</p>
        @endif
    </div>

    @php
        $hasPublishedResults = $competition->results
            ->contains(fn ($r) => filled(trim((string) ($r->place ?? ''))));
    @endphp
    @if(auth()->check() && auth()->user()->role === 'teacher' && ! $hasPublishedResults && in_array($competition->status, ['finished', 'ongoing'], true))
        <div class="rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-2 text-xl font-semibold text-gray-800">Результат</h2>
            <p class="mb-4 text-sm text-gray-600">Место ещё не указано. Добавьте результат на странице соревнования.</p>
            <a
                href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results', 'add_result' => 1]) }}"
                class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700"
            >
                Добавить результат
            </a>
        </div>
    @endif
</div>
