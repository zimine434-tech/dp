@php
    $participations = $participations ?? collect();
@endphp
<section
    class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
    aria-labelledby="profile-competitions-heading"
>
    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <h2 id="profile-competitions-heading" class="text-lg font-semibold text-gray-900">
            Соревнования
        </h2>
        <p class="mt-2 text-sm leading-relaxed text-gray-600">
            Завершённые соревнования, в которых участвовал этот пользователь: вид спорта и даты проведения.
        </p>
    </div>

    <div class="p-6">
        @if($participations->isEmpty())
            <p class="text-sm text-gray-500">Нет завершённых соревнований для отображения.</p>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                @foreach($participations as $participant)
                    @php
                        $comp = $participant->competition;
                    @endphp
                    <article class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <div class="mb-3 flex items-start justify-between gap-2">
                                <h3 class="flex-1 text-lg font-bold leading-tight text-gray-900">
                                    <a href="{{ route('guest.competitions.show', $comp) }}" class="transition hover:text-blue-600">
                                        {{ $comp->name }}
                                    </a>
                                </h3>
                            </div>

                            <div class="mb-4 space-y-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $comp->sport?->name ?? 'Не указан' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>
                                        @if($comp->start_date && $comp->end_date)
                                            @if($comp->start_date->format('Y-m-d') === $comp->end_date->format('Y-m-d'))
                                                {{ $comp->start_date->format('d.m.Y') }}
                                            @else
                                                {{ $comp->start_date->format('d.m.Y') }} — {{ $comp->end_date->format('d.m.Y') }}
                                            @endif
                                        @elseif($comp->start_date)
                                            с {{ $comp->start_date->format('d.m.Y') }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <a
                                href="{{ route('guest.competitions.show', $comp) }}"
                                class="mt-auto block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Подробнее
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
