@extends('layouts.guest')

@section('title', 'Новости')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Новости</h1>
        </div>

        <!-- Фильтры -->
        <form method="GET" action="{{ route('guest.news') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" data-news-filters>
            <input type="hidden" name="page" value="1">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-6 sm:items-end">
                <div class="sm:col-span-3">
                    <label for="news_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск</label>
                    <input
                        id="news_q"
                        type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        data-news-q
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                        placeholder="По названию или тексту новости"
                    >
                </div>
                <div class="sm:col-span-1">
                    <label for="news_date_from" class="mb-1 block text-sm font-medium text-gray-700">С</label>
                    <input
                        id="news_date_from"
                        type="date"
                        name="date_from"
                        value="{{ $dateFrom ?? '' }}"
                        data-news-date-from
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                    >
                </div>
                <div class="sm:col-span-1">
                    <label for="news_date_to" class="mb-1 block text-sm font-medium text-gray-700">По</label>
                    <input
                        id="news_date_to"
                        type="date"
                        name="date_to"
                        value="{{ $dateTo ?? '' }}"
                        data-news-date-to
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                    >
                </div>
                <div class="sm:col-span-1 flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        Применить
                    </button>
                    <a
                        href="{{ route('guest.news') }}"
                        class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Сбросить
                    </a>
                </div>
            </div>
        </form>

        <!-- Новости -->
        <div>
            <div id="news-container">
                @if($publishedNews->count() > 0)
                    <div>
                        @include('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'guest'])
                    </div>

                    @if($publishedNews->hasPages())
                        <div id="news-pagination" class="mt-8 border-t border-gray-200 pt-6">
                            {{ $publishedNews->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        @if(filled($q ?? null) || filled($dateFrom ?? null) || filled($dateTo ?? null))
                            <p class="mt-2 text-sm font-medium text-gray-900">По вашему запросу ничего не найдено</p>
                            <p class="mt-1 text-sm text-gray-500">Измените поиск или нажмите «Сбросить».</p>
                        @else
                            <p class="mt-2 text-sm text-gray-500">Нет опубликованных новостей</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filtersForm = document.querySelector('[data-news-filters]');
            const qInput = document.querySelector('[data-news-q]');
            const dateFromInput = document.querySelector('[data-news-date-from]');
            const dateToInput = document.querySelector('[data-news-date-to]');

            // Обработка пагинации для новостей
            const newsPagination = document.getElementById('news-pagination');
            if (newsPagination) {
                newsPagination.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.href && !link.hasAttribute('data-turbo')) {
                        e.preventDefault();
                        loadPage(link.href);
                    }
                });
            }

            function buildUrl(pageUrl) {
                const base = pageUrl ? new URL(pageUrl) : new URL(filtersForm ? filtersForm.action : window.location.href);
                if (!pageUrl) base.searchParams.delete('page');

                const q = (qInput ? qInput.value : '').trim();
                const df = (dateFromInput ? dateFromInput.value : '').trim();
                const dt = (dateToInput ? dateToInput.value : '').trim();

                if (q) base.searchParams.set('q', q); else base.searchParams.delete('q');
                if (df) base.searchParams.set('date_from', df); else base.searchParams.delete('date_from');
                if (dt) base.searchParams.set('date_to', dt); else base.searchParams.delete('date_to');

                return base;
            }

            function loadPage(url) {
                // Показываем индикатор загрузки
                const container = document.getElementById('news-container');
                const originalContent = container.innerHTML;
                container.innerHTML = '<div class="p-12 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-4 text-sm text-gray-500">Загрузка...</p></div>';

                // Создаем URL с параметрами пагинации
                const urlObj = buildUrl(url).toString();
                const u = new URL(urlObj);
                
                // Добавляем параметр для AJAX-запроса
                u.searchParams.set('ajax', '1');

                fetch(u.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    
                    // Проверяем, содержит ли HTML класс grid (значит есть контент)
                    if (data.html.includes('grid grid-cols-1')) {
                        html = '<div>' + data.html + '</div>';
                    } else {
                        html = data.html;
                    }
                    
                    if (data.pagination) {
                        html += '<div id="news-pagination" class="mt-8 border-t border-gray-200 pt-6">' + data.pagination + '</div>';
                    }
                    
                    container.innerHTML = html;
                    
                    // Переподключаем обработчик событий для пагинации
                    const paginationDiv = document.getElementById('news-pagination');
                    if (paginationDiv) {
                        paginationDiv.addEventListener('click', function(e) {
                            const link = e.target.closest('a');
                            if (link && link.href && !link.hasAttribute('data-turbo')) {
                                e.preventDefault();
                                loadPage(link.href);
                            }
                        });
                    }

                    // Обновляем URL в адресной строке (без ajax=1)
                    try {
                        const pretty = buildUrl(url).toString();
                        window.history.replaceState({}, '', pretty);
                    } catch (e) {}
                })
                .catch(error => {
                    console.error('Ошибка загрузки:', error);
                    container.innerHTML = originalContent;
                    alert('Произошла ошибка при загрузке страницы. Пожалуйста, попробуйте еще раз.');
                });
            }

            if (filtersForm) {
                filtersForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    loadPage(buildUrl(null).toString());
                });
            }
        });
    </script>
@endsection

