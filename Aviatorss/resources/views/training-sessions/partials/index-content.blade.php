@props([
    'sessions',
    'filter' => 'all',
    'hasSearchFilters' => false,
    'view' => 'list',
])

<div
    id="training-sessions-content"
    class="space-y-6 transition-opacity duration-150"
    role="region"
    aria-label="Список тренировок"
>
    @if($hasSearchFilters && $sessions->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
            По заданным условиям ничего не найдено.
            <a href="{{ route('training-sessions.index', array_filter(['filter' => $filter !== 'all' ? $filter : null])) }}" data-training-sessions-listing-ajax="1" data-training-reset-empty-listing="1" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>.
        </div>
    @elseif($sessions->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
            @if($filter === 'upcoming')
                Нет предстоящих тренировок.
            @elseif($filter === 'in_progress')
                Нет тренировок, которые идут сейчас.
            @elseif($filter === 'completed')
                Нет завершённых тренировок.
            @elseif($filter === 'cancelled')
                Нет отменённых тренировок.
            @else
                Пока нет ни одной тренировочной сессии — создайте первую.
            @endif
        </div>
    @else
        <div data-training-sessions-list-wrap class="{{ $view === 'cards' ? 'hidden' : '' }}">
            <div class="overflow-x-auto rounded-lg bg-white shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Название</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden md:table-cell">Спорт / команда</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Дата и время</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden lg:table-cell">Локация</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Статус</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($sessions as $session)
                            @include('training-sessions.partials.session-row', ['session' => $session])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div data-training-sessions-cards-wrap class="{{ $view === 'list' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($sessions as $session)
                    <div class="min-w-0">
                        @include('training-sessions.partials.session-card-teacher', ['session' => $session])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
