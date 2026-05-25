@props(['view' => 'list', 'q' => ''])

@php
    $sportViewLinkParams = function (string $mode) use ($q) {
        return array_filter([
            'q' => $q !== '' ? $q : null,
            'view' => $mode === 'cards' ? 'cards' : null,
        ], fn ($v) => $v !== null && $v !== '');
    };
@endphp

<div
    class="flex h-full w-full shrink-0 flex-col justify-end rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md sm:w-auto"
    role="group"
    aria-label="Вид отображения видов спорта"
>
    <div>
        <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Вид</span>
        <div class="inline-flex h-10 w-full items-center rounded-lg border border-gray-200 bg-gray-50 p-0.5 sm:w-auto" role="tablist">
            <a
                href="{{ route('sports.index', $sportViewLinkParams('list')) }}"
                role="tab"
                aria-selected="{{ $view === 'list' ? 'true' : 'false' }}"
                class="inline-flex h-9 flex-1 items-center justify-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:flex-none {{ $view === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}"
            >
                Список
            </a>
            <a
                href="{{ route('sports.index', $sportViewLinkParams('cards')) }}"
                role="tab"
                aria-selected="{{ $view === 'cards' ? 'true' : 'false' }}"
                class="inline-flex h-9 flex-1 items-center justify-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:flex-none {{ $view === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}"
            >
                Карточки
            </a>
        </div>
    </div>
</div>
