@extends('layouts.teacher')

@section('title', 'Новости')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Новости</h1>
            </div>
            <a
                href="{{ route('news.create') }}"
                class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap"
            >
                <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="hidden sm:inline">Создать новость</span>
                <span class="sm:hidden">Создать</span>
            </a>
        </div>

        <!-- Сообщения об успехе -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form
            id="news-filters-form"
            method="get"
            action="{{ route('news.index') }}"
            data-live-news-filters="1"
            class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
        >
            <div class="w-full min-w-0 sm:w-52">
                <label for="news_status_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Статус</label>
                <x-news-status-filter-combobox :value="$newsStatus ?? 'all'" />
            </div>
            <div class="min-w-0 flex-1 sm:min-w-[12rem]">
                <label for="news_filter_q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Поиск по названию</label>
                <input
                    id="news_filter_q"
                    type="search"
                    name="q"
                    value="{{ $q ?? '' }}"
                    maxlength="255"
                    placeholder="Введите название…"
                    autocomplete="off"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>
            <div class="flex w-full shrink-0 gap-2 sm:w-auto sm:items-end">
                <button type="submit" class="sr-only">Применить фильтры</button>
                <button
                    type="button"
                    id="news-filters-reset"
                    class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-200 sm:flex-none"
                >Сбросить</button>
            </div>
        </form>

        <section
            id="published-news-section"
            class="space-y-4 {{ $showPublished ? '' : 'hidden' }}"
            @unless($showPublished) aria-hidden="true" @endunless
        >
            <h2 class="flex flex-wrap items-center gap-2 text-xl font-semibold text-gray-900">
                Опубликованные новости
                <span id="published-news-total-badge" class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                    {{ $publishedTotal ?? 0 }}
                </span>
            </h2>

            <div id="published-news-container" class="space-y-4">
                @if($showPublished)
                    @if($publishedNews->count() > 0)
                        @include('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'published'])

                        @if($publishedNews->hasPages())
                            <div id="published-pagination" class="flex justify-end pt-1">
                                {{ $publishedNews->links() }}
                            </div>
                        @endif
                    @else
                        @include('news.partials.news-empty-teacher', ['variant' => 'published', 'hasSearch' => ($q ?? '') !== ''])
                    @endif
                @endif
            </div>
        </section>

        <section
            id="draft-news-section"
            class="space-y-4 {{ $showDraft ? '' : 'hidden' }}"
            @unless($showDraft) aria-hidden="true" @endunless
        >
            <h2 class="flex flex-wrap items-center gap-2 text-xl font-semibold text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Черновики
                <span id="draft-news-total-badge" class="rounded-full bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-800">
                    {{ $draftTotal ?? 0 }}
                </span>
            </h2>

            <div id="draft-news-container" class="space-y-4">
                @if($showDraft)
                    @if($draftNews->count() > 0)
                        @include('news.partials.news-grid', ['news' => $draftNews, 'type' => 'draft'])

                        @if($draftNews->hasPages())
                            <div id="draft-pagination" class="flex justify-end pt-1">
                                {{ $draftNews->links() }}
                            </div>
                        @endif
                    @else
                        @include('news.partials.news-empty-teacher', ['variant' => 'draft', 'hasSearch' => ($q ?? '') !== ''])
                    @endif
                @endif
            </div>
        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('news-filters-form');
            if (!form || !form.dataset.liveNewsFilters) return;

            var qInput = document.getElementById('news_filter_q');
            var statusHidden = form.querySelector('input[name="news_status"]');
            var publishedSection = document.getElementById('published-news-section');
            var draftSection = document.getElementById('draft-news-section');
            var publishedBadge = document.getElementById('published-news-total-badge');
            var draftBadge = document.getElementById('draft-news-total-badge');
            var resetBtn = document.getElementById('news-filters-reset');
            var debounceTimer = null;
            var abortController = null;

            function buildListingsUrl() {
                var url = new URL(form.action, window.location.origin);
                var params = new URLSearchParams(new FormData(form));
                params.forEach(function (value, key) {
                    if (value === '') params.delete(key);
                });
                if (params.get('news_status') === 'all') {
                    params.delete('news_status');
                }
                url.search = params.toString();
                return url;
            }

            function bindPagination(containerId) {
                var el = document.getElementById(containerId);
                if (!el) return;
                el.addEventListener('click', function (e) {
                    var link = e.target.closest('a');
                    if (!link || !link.href || link.hasAttribute('data-turbo')) return;
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                    e.preventDefault();
                    refreshFromUrl(new URL(link.href, window.location.origin));
                });
            }

            function applyListingPayload(data) {
                var m = data.meta || {};
                if (publishedBadge && typeof m.published_total !== 'undefined') {
                    publishedBadge.textContent = String(m.published_total);
                }
                if (draftBadge && typeof m.draft_total !== 'undefined') {
                    draftBadge.textContent = String(m.draft_total);
                }

                if (publishedSection) {
                    if (m.show_published) {
                        publishedSection.classList.remove('hidden');
                        publishedSection.removeAttribute('aria-hidden');
                    } else {
                        publishedSection.classList.add('hidden');
                        publishedSection.setAttribute('aria-hidden', 'true');
                    }
                }
                if (draftSection) {
                    if (m.show_draft) {
                        draftSection.classList.remove('hidden');
                        draftSection.removeAttribute('aria-hidden');
                    } else {
                        draftSection.classList.add('hidden');
                        draftSection.setAttribute('aria-hidden', 'true');
                    }
                }

                var pubC = document.getElementById('published-news-container');
                if (pubC) {
                    if (m.show_published) {
                        var html = data.published.html || '';
                        if (data.published.pagination) {
                            html += '<div id="published-pagination" class="flex justify-end pt-1">' + data.published.pagination + '</div>';
                        }
                        pubC.innerHTML = html;
                        bindPagination('published-pagination');
                    } else {
                        pubC.innerHTML = '';
                    }
                }

                var dC = document.getElementById('draft-news-container');
                if (dC) {
                    if (m.show_draft) {
                        var dhtml = data.draft.html || '';
                        if (data.draft.pagination) {
                            dhtml += '<div id="draft-pagination" class="flex justify-end pt-1">' + data.draft.pagination + '</div>';
                        }
                        dC.innerHTML = dhtml;
                        bindPagination('draft-pagination');
                    } else {
                        dC.innerHTML = '';
                    }
                }
            }

            function refreshFromUrl(sourceUrl) {
                var url = sourceUrl ? new URL(sourceUrl.toString()) : buildListingsUrl();
                url.searchParams.set('ajax', '1');

                if (abortController) abortController.abort();
                abortController = new AbortController();

                var pubC = document.getElementById('published-news-container');
                var dC = document.getElementById('draft-news-container');
                var spinner = '<div class="py-8 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-3 text-sm text-gray-500">Загрузка...</p></div>';
                if (pubC) pubC.innerHTML = spinner;
                if (dC) dC.innerHTML = spinner;

                fetch(url.toString(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    signal: abortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                })
                    .then(function (res) {
                        return res.json();
                    })
                    .then(function (data) {
                        applyListingPayload(data);
                        var displayUrl = new URL(url.toString());
                        displayUrl.searchParams.delete('ajax');
                        var path = displayUrl.pathname + (displayUrl.search ? displayUrl.search : '');
                        if (window.location.pathname + window.location.search !== path) {
                            history.replaceState(null, '', path);
                        }
                    })
                    .catch(function (e) {
                        if (e.name === 'AbortError') return;
                        console.error('Ошибка загрузки новостей:', e);
                        alert('Не удалось обновить список. Попробуйте ещё раз.');
                    });
            }

            function scheduleDebouncedRefresh() {
                clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(function () {
                    debounceTimer = null;
                    refreshFromUrl();
                }, 320);
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(debounceTimer);
                debounceTimer = null;
                refreshFromUrl();
            });

            if (qInput) {
                qInput.addEventListener('input', scheduleDebouncedRefresh);
                qInput.addEventListener('search', scheduleDebouncedRefresh);
            }

            if (statusHidden) {
                statusHidden.addEventListener('change', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = null;
                    refreshFromUrl();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    debounceTimer = null;
                    if (qInput) qInput.value = '';
                    if (statusHidden) {
                        statusHidden.value = 'all';
                    }
                    document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
                    refreshFromUrl(new URL(form.action, window.location.origin));
                });
            }

            bindPagination('published-pagination');
            bindPagination('draft-pagination');
        });
    </script>
@endsection
