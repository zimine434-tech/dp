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
                <div class="flex items-center gap-2">
                    <label for="student_group_filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">Группа:</label>
                    <select
                        id="student_group_filter"
                        name="group"
                        onchange="this.form.submit()"
                        class="min-w-[10rem] rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-none outline-none focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="all" @selected(($groupFilter ?? 'all') === 'all')>Все группы</option>
                        @foreach($allGroups as $groupName)
                            <option value="{{ $groupName }}" @selected(($groupFilter ?? '') === $groupName)>{{ $groupName }}</option>
                        @endforeach
                        @if(($studentsWithoutGroupCount ?? 0) > 0)
                            <option value="no-group" @selected(($groupFilter ?? '') === 'no-group')>Без группы</option>
                        @endif
                    </select>
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

            var debounceMs = 320;
            var timer = null;
            var aborter = null;

            function paramsFromForm() {
                var fd = new FormData(form);
                var p = new URLSearchParams(fd);
                p.set('fragment', '1');
                return p;
            }

            function syncUrl() {
                var p = paramsFromForm();
                p.delete('fragment');
                var qs = p.toString();
                var u = form.action + (qs ? ('?' + qs) : '');
                history.replaceState(null, '', u);
            }

            function refresh() {
                if (aborter) aborter.abort();
                aborter = new AbortController();
                results.classList.add('opacity-60', 'pointer-events-none');
                var p = paramsFromForm();
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
                    })
                    .catch(function (err) {
                        if (err.name === 'AbortError') return;
                    })
                    .finally(function () {
                        results.classList.remove('opacity-60', 'pointer-events-none');
                    });
            }

            lastnameInput.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(refresh, debounceMs);
            });
        })();
    </script>
@endpush
