@extends('layouts.teacher')

@section('title', 'Студенты')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Студенты</h1>
            </div>

            <form id="students-filter-form" method="get" action="{{ route('students.index') }}" class="flex flex-col lg:flex-row flex-wrap items-start lg:items-end gap-4">
                <div class="flex w-full sm:w-auto flex-col gap-1 sm:max-w-xs">
                    <label for="student_lastname_search" class="text-sm font-medium text-gray-700">Фамилия</label>
                    <input
                        type="search"
                        name="lastname"
                        id="student_lastname_search"
                        value="{{ $lastnameSearch ?? '' }}"
                        autocomplete="off"
                        placeholder="Начните вводить…"
                        class="min-w-[12rem] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-none outline-none focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                </div>
                @php
                    $studentGroupOptions = [['value' => 'all', 'label' => 'Все группы']];
                    foreach ($allGroups ?? [] as $groupName) {
                        $studentGroupOptions[] = ['value' => (string) $groupName, 'label' => (string) $groupName];
                    }
                    if (($studentsWithoutGroupCount ?? 0) > 0) {
                        $studentGroupOptions[] = ['value' => 'no-group', 'label' => 'Без группы'];
                    }
                @endphp
                <div class="flex w-full flex-col gap-1 sm:w-auto sm:min-w-[12rem]">
                    <label for="student_group_filter_combobox_trigger" class="text-sm font-medium text-gray-700">Группа</label>
                    <x-filter-combobox
                        name="group"
                        :selected="$groupFilter ?? 'all'"
                        :options="$studentGroupOptions"
                        input-id="student_group_filter"
                        variant="filter"
                    />
                </div>

                <fieldset class="m-0 min-w-0 border-0 p-0">
                    <legend class="sr-only">Фильтр по статусу физорга</legend>
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-2">
                        <span class="text-sm font-medium text-gray-700">Статус:</span>
                        <div class="flex flex-wrap gap-3 rounded-lg border border-gray-300 bg-white px-3 py-2 outline-none focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="radio" name="fizorg" value="all" class="accent-blue-600 outline-none focus:ring-0" @checked(($fizorgFilter ?? 'all') === 'all') onchange="this.form.submit()">
                                <span class="text-sm text-gray-700">Все</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="radio" name="fizorg" value="fizorg" class="accent-blue-600 outline-none focus:ring-0" @checked(($fizorgFilter ?? '') === 'fizorg') onchange="this.form.submit()">
                                <span class="text-sm text-gray-700">Физорги</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="radio" name="fizorg" value="not_fizorg" class="accent-blue-600 outline-none focus:ring-0" @checked(($fizorgFilter ?? '') === 'not_fizorg') onchange="this.form.submit()">
                                <span class="text-sm text-gray-700">Не физорги</span>
                            </label>
                        </div>
                    </div>
                </fieldset>

                <input type="hidden" id="students_per_page" name="per_page" value="{{ (int)($perPage ?? 50) }}">
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div id="students-results" class="transition-opacity duration-150" role="region" aria-live="polite" aria-label="Список студентов">
            @include('students.partials.results')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('students-filter-form');
            var results = document.getElementById('students-results');
            var lastnameInput = document.getElementById('student_lastname_search');
            if (!form || !results || !lastnameInput) return;

            var PER_PAGE_STORAGE_KEY = 'students_teacher_per_page';
            var perPageHidden = document.getElementById('students_per_page');

            var debounceMs = 320;
            var timer = null;
            var aborter = null;

            function paramsFromForm(resetPage) {
                var fd = new FormData(form);
                var p = new URLSearchParams(fd);
                if (resetPage) {
                    p.delete('page');
                }
                p.set('fragment', '1');
                return p;
            }

            function syncPerPageBottomFromHidden() {
                if (!perPageHidden) return;
                var bottom = document.getElementById('students_per_page_bottom');
                if (!bottom) return;
                bottom.value = String(perPageHidden.value || '50');
                var root = bottom.closest('[data-filter-combobox]');
                if (root && typeof root._syncFilterCombobox === 'function') {
                    root._syncFilterCombobox();
                }
            }

            function syncUrl() {
                var p = paramsFromForm();
                p.delete('fragment');
                var qs = p.toString();
                var u = form.action + (qs ? ('?' + qs) : '');
                history.replaceState(null, '', u);
            }

            function refresh(resetPage) {
                if (aborter) aborter.abort();
                aborter = new AbortController();
                results.classList.add('opacity-60', 'pointer-events-none');
                var p = paramsFromForm(!!resetPage);
                fetch(form.action + '?' + p.toString(), {
                    signal: aborter.signal,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error('fetch');
                        return res.text();
                    })
                    .then(function (html) {
                        results.innerHTML = html;
                        syncUrl();
                        if (typeof window.initFilterComboboxes === 'function') {
                            window.initFilterComboboxes(results, false);
                        }
                        syncPerPageBottomFromHidden();
                        document.dispatchEvent(new CustomEvent('filter-combobox:sync'));
                    })
                    .catch(function (err) {
                        if (err.name === 'AbortError') return;
                    })
                    .finally(function () {
                        results.classList.remove('opacity-60', 'pointer-events-none');
                    });
            }

            function initPerPageFromStorage() {
                if (!perPageHidden) return false;
                try {
                    var u = new URL(window.location.href);
                    if (!u.searchParams.get('per_page')) {
                        var stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
                        if (stored && stored !== perPageHidden.value) {
                            perPageHidden.value = stored;
                            return true;
                        }
                    }
                } catch (e) {}
                return false;
            }

            if (initPerPageFromStorage()) {
                refresh();
            }

            var groupFilter = document.getElementById('student_group_filter');
            if (groupFilter) {
                groupFilter.addEventListener('change', function () {
                    form.submit();
                });
            }

            document.addEventListener('change', function (e) {
                var target = e.target;
                if (!target || target.id !== 'students_per_page_bottom') return;
                if (!perPageHidden) return;
                perPageHidden.value = String(target.value || '50');
                try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(perPageHidden.value || '50')); } catch (e2) {}
                refresh(true);
            });

            lastnameInput.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(refresh, debounceMs);
            });
        })();
    </script>
@endpush
