@if(($studentsTotalAll ?? 0) === 0)
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Нет студентов</h3>
        <p class="mt-1 text-sm text-gray-500">В системе пока нет зарегистрированных студентов.</p>
    </div>
@elseif(($totalFiltered ?? 0) === 0)
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Никого не найдено</h3>
        <p class="mt-1 text-sm text-gray-500">Измените фильтры или сбросьте их.</p>
        <div class="mt-6">
            <a href="{{ route('students.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">Сбросить фильтры</a>
        </div>
    </div>
@else
    <div class="flex flex-col gap-6">
    @foreach($groupedOnPage as $groupKey => $groupStudents)
        <div class="bg-white rounded-lg shadow-md overflow-hidden student-group" data-group="{{ $groupKey }}">
            @if($groupKey === 'no-group')
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Студенты без группы
                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $groupStudents->count() }}</span>
                    </h2>
                </div>
            @else
                <div class="bg-blue-50 border-b border-blue-200 px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center flex-wrap gap-2">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Группа: {{ $groupKey }}
                            </span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $groupStudents->count() }}</span>
                        </h2>
                        <a href="{{ route('students.groups.schedule', ['groupName' => $groupKey]) }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-blue-200 text-blue-700 hover:bg-blue-100 transition text-sm font-medium">Расписание группы</a>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Отчество</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Логин</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($groupStudents as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->lastname }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->firstname }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->patronymic ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->login }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($student->status_fizorg)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Физорг</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Студент</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('students.show', $student) }}" class="text-blue-600 hover:text-blue-900">Профиль студента</a>
                                        <form action="{{ route('students.toggle-fizorg', $student) }}" method="POST" class="inline" onsubmit="return confirm('{{ $student->status_fizorg ? 'Снять статус физорга у этого студента?' : 'Установить статус физорга для этого студента?' }}')">
                                            @csrf
                                            @foreach(request()->query() as $key => $value)
                                                @if(is_string($value))
                                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                @endif
                                            @endforeach
                                            <button type="submit" class="{{ $student->status_fizorg ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                                {{ $student->status_fizorg ? 'Убрать физорга' : 'Сделать физоргом' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if(($studentListLastPage ?? 1) > 1)
        <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Страницы списка студентов">
            @for($p = 1; $p <= $studentListLastPage; $p++)
                <a href="{{ route('students.index', array_merge(request()->query(), ['page' => $p])) }}" class="inline-flex min-w-[2.5rem] items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition {{ $p === ($studentListCurrentPage ?? 1) ? 'bg-blue-600 text-white shadow-sm' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">{{ $p }}</a>
            @endfor
        </nav>
    @endif
    </div>
@endif
