@php
    $nearestTraining = $nearestTraining ?? null;
    $inSidebar = $inSidebar ?? false;
    $trainingsIndexRoute = auth()->user()->role === 'teacher'
        ? route('training-sessions.index')
        : route('training-sessions.student.index');
    $bodyClass = $inSidebar ? 'p-4' : 'p-5';
    $innerClass = $inSidebar
        ? 'rounded-md bg-blue-50 px-3 py-2.5'
        : 'rounded-lg border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50/80 p-4 ring-1 ring-blue-100/80';
@endphp

<div class="rounded-lg bg-white shadow-md">
    <div class="{{ $bodyClass }}">
        <h2 class="{{ $inSidebar ? 'mb-2 text-base' : 'mb-3 text-lg' }} font-semibold text-gray-800">Ближайшая тренировка</h2>

        @if($nearestTraining)
            <div class="{{ $innerClass }}">
                <h3 class="text-sm font-semibold leading-snug text-gray-900 break-words">
                    <a href="{{ route('training-sessions.show', $nearestTraining) }}" class="transition hover:text-blue-600">
                        {{ $nearestTraining->title }}
                    </a>
                </h3>

                <p class="mt-1.5 text-xs leading-snug text-gray-600">
                    @if($nearestTraining->start_time->format('Y-m-d') === $nearestTraining->end_time->format('Y-m-d'))
                        {{ $nearestTraining->start_time->format('d.m.Y') }},
                        {{ $nearestTraining->start_time->format('H:i') }}–{{ $nearestTraining->end_time->format('H:i') }}
                    @else
                        {{ $nearestTraining->start_time->format('d.m.Y H:i') }}
                        –
                        {{ $nearestTraining->end_time->format('d.m.Y H:i') }}
                    @endif
                    @if($nearestTraining->location)
                        <span class="text-gray-400"> · </span>{{ $nearestTraining->location->location }}
                    @endif
                </p>

                <div class="{{ $inSidebar ? 'mt-2 flex flex-wrap items-center gap-2' : 'mt-3 flex flex-wrap items-center justify-between gap-2' }}">
                    @if($nearestTraining->status === 'scheduled')
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-800">
                            Запланирована
                        </span>
                    @elseif($nearestTraining->status === 'in_progress')
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-[11px] font-medium text-yellow-800">
                            Идёт сейчас
                        </span>
                    @endif
                    <a
                        href="{{ route('training-sessions.show', $nearestTraining) }}"
                        class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-1 text-xs font-medium text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1 {{ $inSidebar ? 'ml-auto' : 'sm:ml-0' }}"
                    >
                        Подробнее
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-md border border-dashed border-gray-200 bg-gray-50/80 px-3 py-4 text-center">
                <p class="text-sm font-medium text-gray-700">Ближайших тренировок нет</p>
                <a href="{{ $trainingsIndexRoute }}" class="mt-2 inline-flex text-xs font-medium text-blue-600 hover:text-blue-800">
                    Все тренировки →
                </a>
            </div>
        @endif
    </div>
</div>
