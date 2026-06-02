@extends('layouts.teacher')

@section('title', ($onlyMine ?? false) ? 'Мои новости' : 'Новости')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ ($onlyMine ?? false) ? 'Мои новости' : 'Новости' }}</h1>
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

        <div class="flex h-full flex-col rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md">
            <form
                id="news-filters-form"
                method="get"
                action="{{ ($onlyMine ?? false) ? route('news.my') : route('news.index') }}"
                data-live-news-filters="1"
                class="flex flex-1 flex-col justify-end"
            >
                <input type="hidden" name="page" value="1">
                <input type="hidden" id="news_per_page" name="per_page" value="{{ (int)($perPage ?? 10) }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end lg:flex-nowrap lg:gap-3 xl:gap-4">
                    <div class="min-w-0 w-full sm:w-52 sm:shrink-0 lg:w-48">
                        <label for="news_status_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Статус</label>
                        <x-news-status-filter-combobox :value="$newsStatus ?? 'all'" />
                    </div>
                    <div class="min-w-0 w-full sm:min-w-[12rem] sm:flex-1 lg:w-52 lg:flex-none lg:shrink-0 xl:w-60">
                        <label for="news_filter_q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Поиск</label>
                        <input
                            id="news_filter_q"
                            type="search"
                            name="q"
                            value="{{ $q ?? '' }}"
                            maxlength="255"
                            placeholder="По названию или тексту…"
                            autocomplete="off"
                            class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                        >
                    </div>
                    <div class="grid min-w-0 w-full grid-cols-2 gap-3 sm:flex sm:min-w-0 sm:shrink sm:gap-3 lg:flex-[0.9] lg:min-w-0 lg:max-w-[20rem]">
                        <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                            <label for="news_date_from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата с</label>
                            <input
                                id="news_date_from"
                                type="date"
                                name="date_from"
                                value="{{ $dateFrom ?? '' }}"
                                class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                            >
                        </div>
                        <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                            <label for="news_date_to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата по</label>
                            <input
                                id="news_date_to"
                                type="date"
                                name="date_to"
                                value="{{ $dateTo ?? '' }}"
                                class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                            >
                        </div>
                    </div>
                    <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                        <button type="submit" class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700 sm:flex-none">
                            Применить
                        </button>
                        <button
                            type="button"
                            id="news-filters-reset"
                            class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none"
                        >Сбросить</button>
                    </div>
                </div>
            </form>
        </div>

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

                        <div id="published-pagination" class="flex justify-end pt-1">
                            <div class="mr-auto flex items-center gap-2">
                                <label for="news_per_page_bottom_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                                <x-per-page-combobox :selected="(int)($perPage ?? 10)" input-id="news_per_page_bottom" />
                            </div>
                            @if($publishedNews->hasPages())
                                {{ $publishedNews->links() }}
                            @endif
                        </div>
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

                        <div id="draft-pagination" class="flex justify-end pt-1">
                            <div class="mr-auto flex items-center gap-2">
                                <label for="news_per_page_bottom_draft_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                                <x-per-page-combobox :selected="(int)($perPage ?? 10)" input-id="news_per_page_bottom_draft" />
                            </div>
                            @if($draftNews->hasPages())
                                {{ $draftNews->links() }}
                            @endif
                        </div>
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
            var perPageSelect = document.getElementById('news_per_page');
            var publishedSection = document.getElementById('published-news-section');
            var draftSection = document.getElementById('draft-news-section');
            var publishedBadge = document.getElementById('published-news-total-badge');
            var draftBadge = document.getElementById('draft-news-total-badge');
            var resetBtn = document.getElementById('news-filters-reset');
            var debounceTimer = null;
            var abortController = null;
            var PER_PAGE_STORAGE_KEY = 'news_teacher_per_page';

            function safeGetPerPage() {
                var v = perPageSelect ? parseInt(String(perPageSelect.value || ''), 10) : NaN;
                return [10, 25, 50, 100].indexOf(v) !== -1 ? v : 10;
            }

            function renderPerPageSelectHtml(selectId) {
                var current = safeGetPerPage();
                if (typeof window.renderPerPageComboboxHtml === 'function') {
                    return window.renderPerPageComboboxHtml(selectId, current);
                }
                return '';
            }

            function buildListingsUrl() {
                var url = new URL(form.action, window.location.origin);
                var params = new URLSearchParams(new FormData(form));
                params.forEach(function (value, key) {
                    if (value === '') params.delete(key);
                });
                if (params.get('news_status') === 'all') {
                    params.delete('news_status');
                }
                params.delete('published');
                params.delete('draft');
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
                        var footer = '<div id="published-pagination" class="flex justify-end pt-1">' +
                            renderPerPageSelectHtml('news_per_page_bottom') +
                            (data.published.pagination ? data.published.pagination : '') +
                            '</div>';
                        html += footer;
                        pubC.innerHTML = html;
                        if (typeof window.initFilterComboboxes === 'function') {
                            window.initFilterComboboxes(pubC);
                        }
                        bindPagination('published-pagination');
                    } else {
                        pubC.innerHTML = '';
                    }
                }

                var dC = document.getElementById('draft-news-container');
                if (dC) {
                    if (m.show_draft) {
                        var dhtml = data.draft.html || '';
                        var dfooter = '<div id="draft-pagination" class="flex justify-end pt-1">' +
                            renderPerPageSelectHtml('news_per_page_bottom_draft') +
                            (data.draft.pagination ? data.draft.pagination : '') +
                            '</div>';
                        dhtml += dfooter;
                        dC.innerHTML = dhtml;
                        if (typeof window.initFilterComboboxes === 'function') {
                            window.initFilterComboboxes(dC);
                        }
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

            if (perPageSelect) {
                // init from localStorage if URL doesn't specify
                try {
                    var u = new URL(window.location.href);
                    if (!u.searchParams.get('per_page')) {
                        var stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
                        if (stored && stored !== perPageSelect.value) {
                            perPageSelect.value = stored;
                        }
                    }
                } catch (e) {}
            }

            // Делегируем change — селекторы пересоздаются после AJAX
            document.addEventListener('change', function (e) {
                var target = e.target;
                if (!target) return;
                if (target.id !== 'news_per_page_bottom' && target.id !== 'news_per_page_bottom_draft') return;
                if (!perPageSelect) return;
                perPageSelect.value = String(target.value || '10');
                try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(perPageSelect.value || '10')); } catch (e2) {}
                clearTimeout(debounceTimer);
                debounceTimer = null;
                refreshFromUrl();
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    debounceTimer = null;
                    if (qInput) qInput.value = '';
                    if (statusHidden) {
                        statusHidden.value = 'all';
                    }
                    try { localStorage.removeItem(PER_PAGE_STORAGE_KEY); } catch (e) {}
                    document.dispatchEvent(new CustomEvent('filter-combobox:sync'));
                    refreshFromUrl(new URL(form.action, window.location.origin));
                });
            }

            bindPagination('published-pagination');
            bindPagination('draft-pagination');
        });
    </script>
@endsection
