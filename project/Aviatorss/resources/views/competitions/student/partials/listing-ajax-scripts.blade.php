@php
    $formId = $formId ?? 'competitions-student-filters-form';
    $viewHiddenId = $viewHiddenId ?? 'comp_filter_view';
    $perPageHiddenId = $perPageHiddenId ?? 'comp_filter_per_page';
    $perPageSelectId = $perPageSelectId ?? 'comp_per_page_select';
    $sortHiddenWrapId = $sortHiddenWrapId ?? 'competitions-student-sort-hidden-inputs';
    $sortStorageKey = $sortStorageKey ?? 'index';
@endphp
<script>
(function () {
    const VIEW_STORAGE_KEY = 'competitions_student_view';
    const PER_PAGE_STORAGE_KEY = 'competitions_student_per_page';
    const SORT_STORAGE_KEY = 'competitions_student_sort_' + @json($sortStorageKey);
    const form = document.getElementById(@json($formId));
    const viewHidden = document.getElementById(@json($viewHiddenId));
    const perPageHidden = document.getElementById(@json($perPageHiddenId));
    const sortHiddenWrapId = @json($sortHiddenWrapId);
    const perPageSelectId = @json($perPageSelectId);

    const indexPath = form
        ? new URL(form.getAttribute('action') || window.location.pathname, window.location.origin).pathname
        : window.location.pathname;

    let debounceTimer = null;
    let abortController = null;

    function getPerPageSelect() {
        return document.getElementById(perPageSelectId);
    }

    function getStoredViewMode() {
        try {
            const v = localStorage.getItem(VIEW_STORAGE_KEY);
            if (v === 'list' || v === 'cards') return v;
        } catch (e) {}
        return null;
    }

    function persistViewMode(mode) {
        try {
            localStorage.setItem(VIEW_STORAGE_KEY, mode === 'cards' ? 'cards' : 'list');
        } catch (e) {}
    }

    function syncViewToForm(mode) {
        if (viewHidden) {
            viewHidden.value = mode === 'cards' ? 'cards' : 'list';
        }
    }

    function getServerViewMode() {
        if (viewHidden && viewHidden.value === 'list') return 'list';
        return 'cards';
    }

    function applyCompetitionViewMode(mode) {
        const isCards = mode === 'cards';
        document.querySelectorAll('[data-competitions-list-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', isCards);
        });
        document.querySelectorAll('[data-competitions-cards-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', !isCards);
        });
        document.querySelectorAll('[data-competitions-cards-sort-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', !isCards);
        });
        const btnList = document.getElementById('competitions-view-list');
        const btnCards = document.getElementById('competitions-view-cards');
        if (btnList && btnCards) {
            btnList.setAttribute('aria-selected', !isCards ? 'true' : 'false');
            btnCards.setAttribute('aria-selected', isCards ? 'true' : 'false');
            btnList.classList.toggle('bg-white', !isCards);
            btnList.classList.toggle('shadow-sm', !isCards);
            btnList.classList.toggle('text-gray-900', !isCards);
            btnList.classList.toggle('text-gray-600', isCards);
            btnCards.classList.toggle('bg-white', isCards);
            btnCards.classList.toggle('shadow-sm', isCards);
            btnCards.classList.toggle('text-gray-900', isCards);
            btnCards.classList.toggle('text-gray-600', !isCards);
        }
    }

    function viewModeFromParams(params) {
        return params.get('view') === 'list' ? 'list' : 'cards';
    }

    function isListingHref(href) {
        try {
            const u = new URL(href, window.location.origin);
            return u.origin === window.location.origin && u.pathname === indexPath;
        } catch (e) {
            return false;
        }
    }

    function getSortWrap() {
        return document.getElementById(sortHiddenWrapId);
    }

    function removeSortParams(url) {
        ['cards_sort', 'cards_order', 'list_sort', 'list_order'].forEach(function (key) {
            while (url.searchParams.has(key)) {
                url.searchParams.delete(key);
            }
        });
    }

    function appendSortParamsFromWrap(url) {
        const wrap = getSortWrap();
        if (!wrap) {
            return;
        }
        removeSortParams(url);
        wrap.querySelectorAll('input[name]').forEach(function (input) {
            if (!input.name) {
                return;
            }
            if (input.name.endsWith('[]')) {
                url.searchParams.append(input.name, input.value);
            } else {
                url.searchParams.set(input.name, input.value);
            }
        });
    }

    function readStacksFromWrap() {
        const wrap = getSortWrap();
        if (!wrap) {
            return { cards: null, list: null };
        }

        function readPrefix(prefix) {
            const scalar = wrap.querySelector('input[name="' + prefix + '_sort"]');
            if (scalar && scalar.value === 'none') {
                return [];
            }
            const fields = Array.from(wrap.querySelectorAll('input[name="' + prefix + '_sort[]"]')).map(function (el) {
                return el.value;
            });
            const orders = Array.from(wrap.querySelectorAll('input[name="' + prefix + '_order[]"]')).map(function (el) {
                return el.value;
            });
            if (fields.length === 0) {
                return null;
            }

            return fields.map(function (field, index) {
                return { field: field, order: orders[index] || 'asc' };
            });
        }

        return { cards: readPrefix('cards'), list: readPrefix('list') };
    }

    function renderStacksToWrap(stored) {
        const wrap = getSortWrap();
        if (!wrap || !stored) {
            return;
        }

        wrap.innerHTML = '';

        function render(prefix, stack) {
            if (stack === null || stack === undefined) {
                return;
            }
            if (!stack.length) {
                const none = document.createElement('input');
                none.type = 'hidden';
                none.name = prefix + '_sort';
                none.value = 'none';
                wrap.appendChild(none);
                return;
            }
            stack.forEach(function (item) {
                const fieldInput = document.createElement('input');
                fieldInput.type = 'hidden';
                fieldInput.name = prefix + '_sort[]';
                fieldInput.value = item.field;
                const orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = prefix + '_order[]';
                orderInput.value = item.order === 'desc' ? 'desc' : 'asc';
                wrap.appendChild(fieldInput);
                wrap.appendChild(orderInput);
            });
        }

        render('cards', stored.cards);
        if (stored.list !== null && stored.list !== undefined) {
            if (stored.list.length > 0) {
                render('list', stored.list);
            }
        }
    }

    function persistSortStacks() {
        const stacks = readStacksFromWrap();
        const payload = {
            cards: stacks.cards,
            list: stacks.list,
        };
        if (payload.list && payload.list.length === 1
            && payload.list[0].field === 'start_date'
            && payload.list[0].order === 'desc') {
            payload.list = null;
        }
        try {
            localStorage.setItem(SORT_STORAGE_KEY, JSON.stringify(payload));
        } catch (e) {}
    }

    function loadSortStacksFromStorage() {
        try {
            const raw = localStorage.getItem(SORT_STORAGE_KEY);
            if (!raw) {
                return null;
            }
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function urlHasExplicitSort(params) {
        return params.has('cards_sort') || params.has('list_sort');
    }

    function buildListUrl(resetPage) {
        const action = form ? (form.getAttribute('action') || window.location.pathname) : window.location.pathname;
        const url = new URL(action, window.location.origin);
        if (form) {
            url.search = new URLSearchParams(new FormData(form)).toString();
        }
        appendSortParamsFromWrap(url);
        if (url.searchParams.get('view') !== 'list') {
            url.searchParams.delete('view');
        }
        if (resetPage) {
            url.searchParams.delete('page');
        }
        return url;
    }

    function replaceSortHiddenInputs(doc) {
        if (!form) return;
        const nextWrap = doc.getElementById(sortHiddenWrapId);
        const liveWrap = document.getElementById(sortHiddenWrapId);
        if (nextWrap && liveWrap) {
            liveWrap.replaceWith(document.importNode(nextWrap, true));
        }
    }

    function applyQueryToForm(params) {
        if (!form) return;
        const filterEl = form.elements.namedItem('filter');
        if (filterEl && params.has('filter')) {
            filterEl.value = params.get('filter');
        }
        if (viewHidden) {
            viewHidden.value = params.get('view') === 'list' ? 'list' : 'cards';
        }
        const perPageEl = form.elements.namedItem('per_page');
        if (perPageEl) {
            perPageEl.value = params.has('per_page') ? params.get('per_page') : '50';
        }
        ['q', 'sport_id', 'competition_category_id', 'date_from', 'date_to'].forEach(function (name) {
            const el = form.elements.namedItem(name);
            if (!el) return;
            el.value = params.has(name) ? params.get(name) : '';
        });
    }

    function syncPerPageUi(params) {
        const perPageSelect = getPerPageSelect();
        if (!perPageHidden || !perPageSelect) return;
        const val = params.has('per_page') ? params.get('per_page') : (perPageHidden.value || '50');
        perPageHidden.value = String(val);
        perPageSelect.value = String(val);
    }

    async function refreshListing(targetUrl) {
        const url = targetUrl || (form ? buildListUrl(false) : new URL(window.location.href));
        const regionEl = document.getElementById('competitions-student-listing-ajax');
        if (!regionEl) return;

        if (abortController) abortController.abort();
        abortController = new AbortController();

        regionEl.classList.add('opacity-60', 'pointer-events-none');
        regionEl.setAttribute('aria-busy', 'true');

        try {
            const res = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                signal: abortController.signal,
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error(String(res.status));

            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextRegion = doc.getElementById('competitions-student-listing-ajax');
            if (!nextRegion) return;

            regionEl.replaceWith(document.importNode(nextRegion, true));

            replaceSortHiddenInputs(doc);
            persistSortStacks();
            if (form) {
                applyQueryToForm(url.searchParams);
                document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
                document.dispatchEvent(new CustomEvent('filter-combobox:sync'));
            }

            const mode = viewModeFromParams(url.searchParams);
            syncViewToForm(mode);
            persistViewMode(mode);
            applyCompetitionViewMode(mode);
            syncPerPageUi(url.searchParams);

            const path = url.pathname + (url.search ? url.search : '');
            if (window.location.pathname + window.location.search !== path) {
                history.replaceState(null, '', path);
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
        } finally {
            const el = document.getElementById('competitions-student-listing-ajax');
            if (el) {
                el.classList.remove('opacity-60', 'pointer-events-none');
                el.removeAttribute('aria-busy');
            }
        }
    }

    function scheduleDebounced() {
        if (!form) return;
        clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () {
            debounceTimer = null;
            refreshListing(buildListUrl(true));
        }, 320);
    }

    function scheduleNow() {
        if (!form) return;
        clearTimeout(debounceTimer);
        debounceTimer = null;
        refreshListing(buildListUrl(true));
    }

    document.addEventListener('change', function (e) {
        const target = e.target;
        if (!target || target.id !== perPageSelectId || !perPageHidden || !form) return;
        const v = parseInt(String(target.value || '50'), 10);
        const val = [10, 25, 50, 100].includes(v) ? v : 50;
        perPageHidden.value = String(val);
        try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(val)); } catch (e) {}
        scheduleNow();
    });

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;

        const viewToggle = e.target.closest('#competitions-view-toolbar .competitions-view-toggle');
        if (viewToggle) {
            const mode = viewToggle.getAttribute('data-view');
            if (mode !== 'list' && mode !== 'cards') return;
            e.preventDefault();
            persistViewMode(mode);
            syncViewToForm(mode);
            applyCompetitionViewMode(mode);
            if (form) {
                const url = buildListUrl(true);
                if (mode === 'list') {
                    url.searchParams.set('view', 'list');
                } else {
                    url.searchParams.delete('view');
                }
                refreshListing(url);
            } else {
                const url = new URL(window.location.href);
                if (mode === 'list') {
                    url.searchParams.set('view', 'list');
                } else {
                    url.searchParams.delete('view');
                }
                const path = url.pathname + (url.search ? url.search : '');
                history.replaceState(null, '', path);
            }
            return;
        }

        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const a = e.target.closest('a[data-competitions-student-ajax]')
            || e.target.closest('#competitions-student-listing-ajax nav a[href]');
        if (!a || !a.href) return;
        if (!isListingHref(a.href)) return;
        e.preventDefault();
        clearTimeout(debounceTimer);
        debounceTimer = null;
        refreshListing(new URL(a.href, window.location.origin));
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            scheduleNow();
        });

        form.querySelectorAll('a[href]').forEach(function (link) {
            if (link.textContent && link.textContent.trim() === 'Сбросить') {
                link.setAttribute('data-competitions-student-ajax', '1');
            }
        });
    }

    let needsInitialRefresh = false;

    (function initSortFromStorage() {
        const url = new URL(window.location.href);
        if (urlHasExplicitSort(url.searchParams)) {
            persistSortStacks();
            return;
        }
        const stored = loadSortStacksFromStorage();
        if (!stored) {
            return;
        }
        const before = JSON.stringify(readStacksFromWrap());
        renderStacksToWrap(stored);
        if (JSON.stringify(readStacksFromWrap()) !== before) {
            needsInitialRefresh = true;
        }
    })();

    (function initPerPageFromStorage() {
        if (!perPageHidden) return;
        const url = new URL(window.location.href);
        const urlVal = url.searchParams.get('per_page');
        let current = urlVal ? parseInt(urlVal, 10) : parseInt(perPageHidden.value || '50', 10);
        if (![10, 25, 50, 100].includes(current)) current = 50;

        if (!urlVal) {
            try {
                const storedPer = parseInt(localStorage.getItem(PER_PAGE_STORAGE_KEY) || '', 10);
                if ([10, 25, 50, 100].includes(storedPer) && storedPer !== current) {
                    perPageHidden.value = String(storedPer);
                    needsInitialRefresh = true;
                }
            } catch (e) {}
        }

        syncPerPageUi(url.searchParams);
    })();

    const serverView = getServerViewMode();
    const storedView = getStoredViewMode();
    if (storedView !== null && storedView !== serverView) {
        syncViewToForm(storedView);
        needsInitialRefresh = true;
    } else {
        persistViewMode(serverView);
        applyCompetitionViewMode(serverView);
    }

    if (needsInitialRefresh && form) {
        scheduleNow();
    }
})();
</script>
