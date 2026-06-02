@props(['view' => 'cards'])

<div
    id="competitions-view-toolbar"
    class="flex h-full w-full shrink-0 flex-col justify-end rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md sm:w-auto"
    role="group"
    aria-label="Вид отображения соревнований"
>
    <div>
        <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Вид</span>
        <div class="inline-flex h-10 w-full items-center rounded-lg border border-gray-200 bg-gray-50 p-0.5 sm:w-auto" role="tablist">
            <button
                type="button"
                id="competitions-view-list"
                class="competitions-view-toggle inline-flex h-9 flex-1 items-center justify-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:flex-none {{ $view === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                data-view="list"
                role="tab"
                aria-selected="{{ $view === 'list' ? 'true' : 'false' }}"
            >
                Список
            </button>
            <button
                type="button"
                id="competitions-view-cards"
                class="competitions-view-toggle inline-flex h-9 flex-1 items-center justify-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:flex-none {{ $view === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                data-view="cards"
                role="tab"
                aria-selected="{{ $view === 'cards' ? 'true' : 'false' }}"
            >
                Карточки
            </button>
        </div>
    </div>
</div>

