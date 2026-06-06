@extends('layouts.teacher')

@section('title', 'Детали соревнования')

@push('styles')
<style>
    .results-listing-table tbody tr.results-listing-row:hover > td {
        background-color: #f9fafb;
    }

    #participants-table-wrap:focus,
    #participants-table-wrap:target {
        outline: none;
    }
</style>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Сообщения об успехе/ошибке -->
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

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $competition->name }}</h1>
                    @if(filled($competition->description))
                        @include('partials.rich-text', ['html' => $competition->description, 'class' => 'mt-2 text-sm sm:text-base text-gray-600'])
                    @endif
                </div>
                <div class="flex gap-2">
                    <a 
                        href="{{ $teacherCompetitionListBackUrl }}" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                    >
                        {{ $teacherCompetitionBackLabel }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Основная информация</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                @if(($competition->result_type ?? 'team') !== 'personal')
                    <div>
                        <label class="text-sm font-medium text-gray-500 block mb-1">Вид спорта</label>
                        <p class="text-lg text-gray-900">
                            @if($competition->sport)
                                <a href="{{ route('sports.show', $competition->sport) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $competition->sport->name }}
                                </a>
                            @else
                                <span class="text-gray-500">Не указан</span>
                            @endif
                        </p>
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Категория</label>
                    <p class="text-lg text-gray-900">{{ $competition->category?->name_category ?? 'Не указана' }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Вид участия</label>
                    <p class="text-lg text-gray-900">{{ $competition->resultFormatLabel() }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата начала</label>
                    <p class="text-lg text-gray-900">{{ $competition->start_date->format('d.m.Y') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата окончания</label>
                    <p class="text-lg text-gray-900">{{ $competition->end_date->format('d.m.Y') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Локация</label>
                    <p class="text-lg text-gray-900">{{ $competition->location->location ?? 'Не указана' }}</p>
                    @if($competition->location && filled($competition->location->address))
                        <p class="text-sm text-gray-500 mt-1">Адрес: {{ $competition->location->address }}</p>
                    @endif
                    @if($competition->location && $competition->location->organizer)
                        <p class="text-sm text-gray-500">Организатор: {{ $competition->location->organizer }}</p>
                    @endif
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Статус</label>
                    <div class="mt-1">
                        @if($competition->status === 'upcoming')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Предстоящее
                            </span>
                        @elseif($competition->status === 'ongoing')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Идет
                            </span>
                        @elseif($competition->status === 'finished')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Завершено
                            </span>
                        @elseif($competition->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Отменено
                            </span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        @include('competitions.partials.applications-pending', ['competition' => $competition])

        <div class="bg-white rounded-lg shadow-md">
            <div>
                <nav class="flex -mb-px" aria-label="Tabs">
                    <button
                        type="button"
                        onclick="switchCompetitionTeacherTab('participants')"
                        id="tab-participants"
                        class="tab-button active px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600"
                    >
                        Участники
                    </button>
                    <button
                        type="button"
                        onclick="switchCompetitionTeacherTab('forms')"
                        id="tab-forms"
                        class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent"
                    >
                        Форма
                    </button>
                    <button
                        type="button"
                        onclick="switchCompetitionTeacherTab('admission')"
                        id="tab-admission"
                        class="tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent"
                    >
                        Допуск к соревнованию
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <div id="content-participants" class="tab-content">
        <!-- Участники -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                @php
                    $count = $competition->participants->count();
                    $lastDigit = $count % 10;
                    $lastTwoDigits = $count % 100;
                    
                    if ($count === 0 || ($lastTwoDigits >= 5 && $lastTwoDigits <= 20) || $lastDigit >= 5 || $lastDigit === 0) {
                        $text = 'Участников';
                    } elseif ($lastDigit === 1) {
                        $text = 'Участник';
                    } else {
                        $text = 'Участника';
                    }
                @endphp
                <h2 class="text-xl font-semibold text-gray-800">
                    <span id="participants-title">{{ $text }} ({{ $count }})</span>
                </h2>
                
                @if($competition->status === 'upcoming')
                    <button 
                        onclick="document.getElementById('add-participant-form').classList.toggle('hidden')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        Добавить участника
                    </button>
                @endif
            </div>

            <!-- Форма добавления участника -->
            @if($competition->status === 'upcoming')
                <div id="add-participant-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 overflow-visible">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Добавить участника</h3>
                    <form id="add-participant-submit-form" action="{{ route('competitions.participants.add', $competition) }}" method="POST" class="space-y-4 overflow-visible">
                        @csrf
                        @include('competitions.partials.show-context-fields')
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Выберите студента <span class="text-red-500">*</span>
                            </label>
                            <div class="relative overflow-visible">
                                <input type="hidden" name="student_data" id="student_data" value="{{ old('student_data') }}">
                                
                                @php
                                    $selectedStudentData = null;
                                    if (old('student_data')) {
                                        $decoded = json_decode(old('student_data'), true);
                                        if (json_last_error() === JSON_ERROR_NONE) {
                                            $selectedStudentData = $decoded;
                                        }
                                    }
                                @endphp

                                <button 
                                    type="button"
                                    id="student-select-button"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between"
                                    onclick="toggleStudentDropdown()"
                                >
                                    <span id="student-select-text" class="text-gray-700 whitespace-nowrap overflow-hidden text-ellipsis">
                                        @if($selectedStudentData)
                                            {{ $selectedStudentData['lastname'] ?? '' }} {{ $selectedStudentData['firstname'] ?? '' }} {{ $selectedStudentData['patronymic'] ?? '' }} ({{ $selectedStudentData['login'] ?? '' }})
                                        @else
                                            Начните вводить ФИО студента
                                        @endif
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div id="student-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                                    <div class="p-2 border-b border-gray-200 flex-shrink-0">
                                        <input 
                                            type="text" 
                                            id="student-search"
                                            placeholder="Например: Иванов Иван"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            oninput="filterStudents(this.value)"
                                            onclick="event.stopPropagation()"
                                        >
                                    </div>
                                    
                                    <div id="student-list" class="overflow-y-auto flex-1 min-h-0"></div>
                                    
                                    <div id="student-loading" class="hidden px-4 py-2 text-sm text-gray-500 text-center border-t border-gray-200">
                                        Поиск...
                                    </div>
                                    
                                    <div id="student-initial-message" class="px-4 py-2 text-sm text-gray-500 text-center border-t border-gray-200">
                                        Введите минимум 2 символа
                                    </div>
                                    
                                    <div id="student-no-results" class="hidden px-4 py-2 text-sm text-gray-500 text-center border-t border-gray-200">
                                        Ничего не найдено
                                    </div>
                                </div>
                            </div>
                            @error('student_data')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(($competition->result_type ?? 'team') === 'personal')
                            <div>
                                <label for="participant_team_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Команда (дисциплина) <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="participant_team_id"
                                    name="team_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('team_id') border-red-500 @enderror"
                                    required
                                >
                                    <option value="">Выберите команду</option>
                                    @foreach($teams as $teamOption)
                                        <option value="{{ $teamOption->id }}" @selected((string) old('team_id') === (string) $teamOption->id)>
                                            {{ $teamOption->name }}@if($teamOption->sport) — {{ $teamOption->sport->name }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('team_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif


                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button 
                                type="button"
                                onclick="document.getElementById('add-participant-form').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Добавить участника
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <p id="participants-empty" class="text-gray-500 {{ $competition->participants->count() > 0 ? 'hidden' : '' }}">Пока нет участников в этом соревновании.</p>
            <div id="participants-table-wrap" class="overflow-x-auto outline-none {{ $competition->participants->count() > 0 ? '' : 'hidden' }}">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Группа</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вид спорта</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="participants-tbody" class="bg-white divide-y divide-gray-200">
                        @foreach($competition->participants->filter(fn ($p) => ($p->role ?? 'student') === 'student') as $participant)
                            @include('competitions.partials.participant-row', ['competition' => $competition, 'participant' => $participant])
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(session('participants_error'))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="text-sm text-red-700">{{ session('participants_error') }}</p>
                </div>
            @endif
            <div id="participants-inline-error" class="hidden mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                <p id="participants-inline-error-text" class="text-sm text-red-700"></p>
            </div>
        </div>

        @php
            $responsibleTeacherUser = $competition->teacher?->user;
            $responsibleTeacherLabel = $responsibleTeacherUser
                ? trim($responsibleTeacherUser->lastname.' '.$responsibleTeacherUser->firstname.' '.($responsibleTeacherUser->patronymic ?? ''))
                : '';
        @endphp
        <div
            id="competition-teacher-block"
            class="bg-white rounded-lg shadow-md p-6 mt-6"
            data-teacher-id="{{ $responsibleTeacherUser?->id ?? '' }}"
            data-teacher-label="{{ e($responsibleTeacherLabel) }}"
        >
            @include('competitions.partials.teacher-block', ['competition' => $competition])

            @if(session('teacher_error'))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="text-sm text-red-700">{{ session('teacher_error') }}</p>
                </div>
            @endif
            <div id="teacher-inline-error" class="hidden mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                <p id="teacher-inline-error-text" class="text-sm text-red-700"></p>
            </div>

            @if($competition->status === 'upcoming')
                <div id="assign-teacher-form" class="hidden mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200 overflow-visible">
                    <form id="assign-teacher-submit-form" action="{{ route('competitions.teacher.update', $competition) }}" method="POST" class="space-y-4 overflow-visible">
                        @csrf
                        @include('competitions.partials.show-context-fields')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Выберите преподавателя <span class="text-red-500">*</span>
                            </label>
                            <div class="relative overflow-visible">
                                <input type="hidden" name="student_data" id="teacher_data" value="">

                                <button
                                    type="button"
                                    id="teacher-select-button"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between"
                                    onclick="toggleTeacherDropdown()"
                                >
                                    <span id="teacher-select-text" class="text-gray-700 whitespace-nowrap overflow-hidden text-ellipsis">
                                        Начните вводить ФИО преподавателя
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div id="teacher-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                                    <div class="p-2 border-b border-gray-200 flex-shrink-0">
                                        <input
                                            type="text"
                                            id="teacher-search"
                                            placeholder="Например: Иванов Иван"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            oninput="filterTeachers(this.value)"
                                            onclick="event.stopPropagation()"
                                        >
                                    </div>

                                    <div id="teacher-list" class="overflow-y-auto flex-1 min-h-0"></div>

                                    <div id="teacher-loading" class="hidden px-4 py-2 text-sm text-gray-500 text-center border-t border-gray-200">
                                        Поиск...
                                    </div>

                                    <div id="teacher-initial-message" class="px-4 py-2 text-sm text-gray-500 text-center border-t border-gray-200">
                                        Введите минимум 2 символа
                                    </div>

                                    <div id="teacher-no-results" class="hidden px-4 py-2 text-sm text-gray-500 text-center border-t border-gray-200">
                                        Ничего не найдено
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button
                                type="button"
                                onclick="document.getElementById('assign-teacher-form').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
                </div>

                <div id="content-forms" class="tab-content hidden">
        <!-- Форма для студентов -->
        <div class="bg-white rounded-lg shadow-md p-6">
            @php
                $studentParticipants = $competition->participants->filter(function ($p) {
                    return ($p->role ?? 'student') === 'student';
                });
                $formsByUserId = $competition->forms->keyBy('user_id');
                $competitionFormsReturnOnly = $competition->formsReturnStatusEditable();
                $competitionFormsFullEdit = $competition->formsAreEditable();
                $competitionFormsLocked = $competition->status === 'cancelled';
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Форма для студентов</h2>
                @if($competitionFormsFullEdit)
                    <button
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        onclick="document.getElementById('create-form-type').classList.toggle('hidden')"
                    >
                        Создать вид формы
                    </button>
                @endif
            </div>

            @if($competitionFormsReturnOnly)
                <p class="mb-4 text-sm text-gray-600">
                    Соревнование завершено — выдача, вид и номер формы не редактируются; можно изменить только «Сдал» / «Не сдал».
                </p>
            @elseif($competitionFormsLocked)
                <p class="mb-4 text-sm text-gray-600">
                    Соревнование отменено — данные формы нельзя изменить.
                </p>
            @endif

            <div id="create-form-type" class="hidden mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <form id="competition-form-types-form" action="{{ route('competitions.form-types.store', $competition) }}" method="POST" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
                    @csrf
                    <div class="flex-1">
                        <label for="new_form_type_name" class="block text-sm font-medium text-gray-700 mb-1">Название вида формы</label>
                        <input
                            id="new_form_type_name"
                            type="text"
                            name="name"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Например: Спортивная форма"
                            required
                        >
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Создать
                    </button>
                </form>
            </div>

            <div id="competition-forms-flash" class="hidden mb-6 rounded border-l-4 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg id="competition-forms-flash-icon" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p id="competition-forms-flash-text" class="text-sm"></p>
                    </div>
                </div>
            </div>

            <p id="competition-forms-empty" class="text-gray-500 {{ $studentParticipants->count() > 0 ? 'hidden' : '' }}">В этом соревновании пока нет студентов.</p>
            <div id="competition-forms-wrap" class="{{ $studentParticipants->count() > 0 ? '' : 'hidden' }}">
                <form
                    id="competition-forms-form"
                    @unless($competitionFormsLocked)
                        action="{{ route('competitions.forms.store', $competition) }}"
                        method="POST"
                    @endunless
                    class="space-y-4"
                    @if($competitionFormsLocked) data-forms-locked="1" @endif
                    @if($competitionFormsReturnOnly) data-forms-return-only="1" @endif
                >
                    @unless($competitionFormsLocked)
                        @csrf
                    @endunless
                    <table class="w-full table-fixed divide-y divide-gray-200">
                        <colgroup>
                            <col style="width: 18%">
                            <col style="width: 14%">
                            <col style="width: 32%">
                            <col style="width: 12%">
                            <col style="width: 14%">
                        </colgroup>
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участник</th>
                                <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Выдана</th>
                                <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вид формы</th>
                                <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер</th>
                                <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сдал</th>
                            </tr>
                        </thead>
                        <tbody id="competition-forms-tbody" class="bg-white divide-y divide-gray-200">
                            @foreach($studentParticipants as $participant)
                                @include('competitions.partials.competition-form-row', [
                                    'competition' => $competition,
                                    'participant' => $participant,
                                    'competitionFormTypes' => $competitionFormTypes ?? collect(),
                                    'form' => $formsByUserId->get((int) $participant->user_id),
                                    'formsLocked' => $competitionFormsLocked,
                                    'formsReturnOnly' => $competitionFormsReturnOnly,
                                ])
                            @endforeach
                        </tbody>
                    </table>

                    @if($competitionFormsFullEdit || $competitionFormsReturnOnly)
                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Сохранить
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
                </div>

                <div id="content-admission" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-md p-6">
            @php
                $admissionStudentParticipants = $competition->participants->filter(function ($p) {
                    return ($p->role ?? 'student') === 'student';
                });
                $admissionStatusEditable = $competition->medicalAdmissionStatusEditable();
                $admissionDocumentEditable = $competition->medicalAdmissionDocumentEditable();
            @endphp

            <h2 class="text-xl font-semibold text-gray-800 mb-2">Допуск к соревнованию</h2>
            <p class="text-sm text-gray-600 mb-4">
                Отметьте по медицинскому заключению врача, допущен студент к соревнованию или нет.
            </p>

            <div id="competition-admission-flash" class="hidden mb-6 rounded p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg id="competition-admission-flash-icon" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p id="competition-admission-flash-text" class="text-sm"></p>
                    </div>
                </div>
            </div>

            <p id="competition-admission-empty" class="text-gray-500 {{ $admissionStudentParticipants->count() > 0 ? 'hidden' : '' }}">В этом соревновании пока нет студентов.</p>
            <div id="competition-admission-wrap" class="{{ $admissionStudentParticipants->count() > 0 ? '' : 'hidden' }}">
                <form id="competition-admission-form" action="{{ route('competitions.medical-admission.store', $competition) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Допуск</th>
                                </tr>
                            </thead>
                            <tbody id="competition-admission-tbody" class="bg-white divide-y divide-gray-200">
                                @foreach($admissionStudentParticipants as $participant)
                                    @include('competitions.partials.competition-admission-row', [
                                        'competition' => $competition,
                                        'participant' => $participant,
                                        'admissionEditable' => $admissionStatusEditable,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($admissionStatusEditable)
                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button
                                type="submit"
                                name="submit_action"
                                value="save_admissions"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Сохранить
                            </button>
                        </div>
                    @endif

                    @if($admissionDocumentEditable)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 mt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Подписанный документ</h3>

                            @if(filled($competition->medical_admission_document_path))
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                    <div id="medical-admission-current-file-text" class="text-sm text-gray-700">
                                        <span class="font-medium">Текущий файл:</span> прикреплён
                                    </div>
                                    <a
                                        id="medical-admission-open-link"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-white transition text-sm"
                                        href="{{ asset('storage/'.$competition->medical_admission_document_path) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Открыть/скачать
                                    </a>
                                </div>
                            @else
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                    <div id="medical-admission-current-file-text" class="text-sm text-gray-700">
                                        <span class="font-medium">Текущий файл:</span> не прикреплён
                                    </div>
                                    <a
                                        id="medical-admission-open-link"
                                        class="hidden inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-white transition text-sm"
                                        href="#"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Открыть/скачать
                                    </a>
                                </div>
                            @endif

                            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                                <div class="flex-1">
                                    <label for="medical_admission_document" class="block text-sm font-medium text-gray-700 mb-1">
                                        @if(filled($competition->medical_admission_document_path))
                                            Заменить файл
                                        @else
                                            Загрузить файл
                                        @endif
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            id="medical_admission_document"
                                            name="medical_admission_document"
                                            type="file"
                                            class="hidden"
                                            accept=".pdf,.png,.jpg,.jpeg,.doc,.docx"
                                        >
                                        <button
                                            type="button"
                                            id="medical-admission-pick-file"
                                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-white transition text-sm"
                                        >
                                            Выбрать файл
                                        </button>
                                        <span id="medical-admission-file-state" class="text-sm text-gray-600">Файл не выбран</span>
                                    </div>
                                    <p id="medical-admission-document-error" class="hidden mt-2 text-sm text-red-600"></p>
                                </div>
                                <button
                                    type="submit"
                                    name="submit_action"
                                    value="attach_document"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                >
                                    Прикрепить документ
                                </button>
                            </div>
                        </div>
                    @elseif(filled($competition->medical_admission_document_path))
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 mt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Подписанный документ</h3>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Текущий файл:</span> прикреплён
                                </p>
                                <a
                                    class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-white transition text-sm"
                                    href="{{ asset('storage/'.$competition->medical_admission_document_path) }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Открыть/скачать
                                </a>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                function participantsCsrfToken() {
                    var el = document.querySelector('meta[name="csrf-token"]');
                    return el ? el.getAttribute('content') : '';
                }

                function setParticipantsError(text) {
                    var box = document.getElementById('participants-inline-error');
                    var txt = document.getElementById('participants-inline-error-text');
                    if (!box || !txt) return;
                    var msg = String(text || '').trim();
                    if (!msg) {
                        txt.textContent = '';
                        box.classList.add('hidden');
                        return;
                    }
                    txt.textContent = msg;
                    box.classList.remove('hidden');
                }

                function participantsCountLabel(count) {
                    var n = count % 10;
                    var n100 = count % 100;
                    if (count === 0 || (n100 >= 5 && n100 <= 20) || n >= 5 || n === 0) {
                        return 'Участников';
                    }
                    if (n === 1) {
                        return 'Участник';
                    }
                    return 'Участника';
                }

                function updateParticipantsTitle(count) {
                    var el = document.getElementById('participants-title');
                    if (!el) return;
                    el.textContent = participantsCountLabel(count) + ' (' + count + ')';
                }

                function appendRowHtml(tbodyId, html) {
                    if (!html) return;
                    var tbody = document.getElementById(tbodyId);
                    if (!tbody) return;
                    var tmp = document.createElement('tbody');
                    tmp.innerHTML = html.trim();
                    var row = tmp.firstElementChild;
                    if (row) {
                        tbody.appendChild(row);
                    }
                }

                function removeParticipantRows(userId) {
                    var id = String(userId);
                    document.querySelectorAll('[data-participant-user-id="' + id + '"], [data-user-id="' + id + '"]').forEach(function (el) {
                        el.remove();
                    });
                }

                function removeApplicationRowForUser(userId) {
                    var appsBody = document.getElementById('competition-applications-tbody');
                    if (!appsBody) return;
                    appsBody.querySelectorAll('[data-application-student-id="' + String(userId) + '"]').forEach(function (el) {
                        el.remove();
                    });
                    var emptyBox = document.getElementById('competition-applications-empty');
                    var tableWrap = document.getElementById('competition-applications-table-wrap');
                    if (appsBody && appsBody.children.length === 0) {
                        if (emptyBox) emptyBox.classList.remove('hidden');
                        if (tableWrap) tableWrap.classList.add('hidden');
                    }
                }

                function initCompetitionFormRow(row) {
                    if (!row) return;
                    if (typeof window.initFilterComboboxes === 'function') {
                        window.initFilterComboboxes(row);
                    }
                    if (typeof window.syncFormRowIssued === 'function') {
                        window.syncFormRowIssued(row);
                    }
                }

                window.syncResponsibleTeacherOrderFields = function (teacherId, teacherLabel) {
                    var block = document.getElementById('competition-teacher-block');
                    var id = teacherId != null && String(teacherId).trim() !== ''
                        ? String(teacherId)
                        : (block && block.dataset.teacherId ? String(block.dataset.teacherId) : '');
                    var label = teacherLabel != null && String(teacherLabel).trim() !== ''
                        ? String(teacherLabel)
                        : (block && block.dataset.teacherLabel ? String(block.dataset.teacherLabel) : '');

                    if (block) {
                        if (id) block.dataset.teacherId = id;
                        if (label) block.dataset.teacherLabel = label;
                    }

                    if (id) {
                        var input1 = document.getElementById('accompanying_teacher');
                        if (input1) input1.value = id;
                        var input2 = document.getElementById('teacher_participant_2');
                        if (input2) input2.value = id;
                    }

                    if (label) {
                        document.querySelectorAll('[data-responsible-teacher-label]').forEach(function (el) {
                            el.textContent = label;
                        });
                    }
                };

                window.competitionOnParticipantAdded = function (data) {
                    if (!data || !data.ok) return;

                    if (data.teacher_html) {
                        var teacherDisplay = document.getElementById('competition-teacher-display');
                        if (teacherDisplay) {
                            var tmp = document.createElement('div');
                            tmp.innerHTML = data.teacher_html.trim();
                            var newDisplay = tmp.querySelector('#competition-teacher-display') || tmp.firstElementChild;
                            if (newDisplay) {
                                teacherDisplay.replaceWith(newDisplay);
                            }
                        }

                        window.syncResponsibleTeacherOrderFields(data.teacher_id, data.teacher_label);
                        return;
                    }

                    if (data.row_html) {
                        var participantsEmpty = document.getElementById('participants-empty');
                        var participantsWrap = document.getElementById('participants-table-wrap');
                        if (participantsEmpty) participantsEmpty.classList.add('hidden');
                        if (participantsWrap) participantsWrap.classList.remove('hidden');
                        appendRowHtml('participants-tbody', data.row_html);
                    }

                    if (typeof data.count === 'number') {
                        updateParticipantsTitle(data.count);
                    }

                    if (data.is_student) {
                        var formsEmpty = document.getElementById('competition-forms-empty');
                        var formsWrap = document.getElementById('competition-forms-wrap');
                        if (formsEmpty) formsEmpty.classList.add('hidden');
                        if (formsWrap) formsWrap.classList.remove('hidden');
                        if (data.forms_row_html) {
                            appendRowHtml('competition-forms-tbody', data.forms_row_html);
                            var formsBody = document.getElementById('competition-forms-tbody');
                            if (formsBody && formsBody.lastElementChild) {
                                initCompetitionFormRow(formsBody.lastElementChild);
                            }
                        }

                        var admissionEmpty = document.getElementById('competition-admission-empty');
                        var admissionWrap = document.getElementById('competition-admission-wrap');
                        if (admissionEmpty) admissionEmpty.classList.add('hidden');
                        if (admissionWrap) admissionWrap.classList.remove('hidden');
                        if (data.admission_row_html) {
                            appendRowHtml('competition-admission-tbody', data.admission_row_html);
                            var admissionBody = document.getElementById('competition-admission-tbody');
                            if (admissionBody && admissionBody.lastElementChild && typeof window.initFilterComboboxes === 'function') {
                                window.initFilterComboboxes(admissionBody.lastElementChild);
                            }
                        }
                    }

                    if (data.user_id) {
                        removeApplicationRowForUser(data.user_id);
                    }
                };

                window.competitionOnParticipantRemoved = function (data) {
                    if (!data || !data.ok) return;
                    if (data.user_id) {
                        removeParticipantRows(data.user_id);
                    }
                    if (typeof data.count === 'number') {
                        updateParticipantsTitle(data.count);
                    }

                    var participantsBody = document.getElementById('participants-tbody');
                    var participantsEmpty = document.getElementById('participants-empty');
                    var participantsWrap = document.getElementById('participants-table-wrap');
                    if (participantsBody && participantsBody.children.length === 0) {
                        if (participantsEmpty) participantsEmpty.classList.remove('hidden');
                        if (participantsWrap) participantsWrap.classList.add('hidden');
                    }

                    if (data.is_student) {
                        var studentCount = typeof data.student_count === 'number' ? data.student_count : 0;
                        var formsEmpty = document.getElementById('competition-forms-empty');
                        var formsWrap = document.getElementById('competition-forms-wrap');
                        if (studentCount === 0) {
                            if (formsEmpty) formsEmpty.classList.remove('hidden');
                            if (formsWrap) formsWrap.classList.add('hidden');
                        }

                        var admissionEmpty = document.getElementById('competition-admission-empty');
                        var admissionWrap = document.getElementById('competition-admission-wrap');
                        if (studentCount === 0) {
                            if (admissionEmpty) admissionEmpty.classList.remove('hidden');
                            if (admissionWrap) admissionWrap.classList.add('hidden');
                        }
                    }
                };

                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('.competition-participant-remove');
                    if (!btn) return;
                    if (!confirm('Вы уверены, что хотите удалить этого участника из соревнования?')) {
                        return;
                    }

                    var url = btn.getAttribute('data-remove-url');
                    if (!url) return;

                    btn.disabled = true;
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': participantsCsrfToken(),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                        .then(function (res) {
                            if (res.ok && res.data && res.data.ok) {
                                window.competitionOnParticipantRemoved(res.data);
                                setParticipantsError('');
                            } else {
                                var msg = (res.data && res.data.message) || 'Не удалось удалить участника.';
                                setParticipantsError(msg);
                                btn.disabled = false;
                            }
                        })
                        .catch(function () {
                            setParticipantsError('Не удалось удалить участника.');
                            btn.disabled = false;
                        });
                });

                var addForm = document.getElementById('add-participant-submit-form');
                if (addForm) {
                    addForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        setParticipantsError('');

                        var studentDataVal = (document.getElementById('student_data') || {}).value || '';
                        if (!studentDataVal) {
                            setParticipantsError('Выберите студента из списка.');
                            return;
                        }

                        var btn = addForm.querySelector('button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.classList.add('opacity-50', 'cursor-not-allowed');
                        }

                        var fd = new FormData(addForm);
                        fetch(addForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': participantsCsrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: fd,
                        })
                            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                            .then(function (res) {
                                if (res.ok && res.data && res.data.ok) {
                                    window.competitionOnParticipantAdded(res.data);

                                    // close form and reset selection
                                    var box = document.getElementById('add-participant-form');
                                    if (box) box.classList.add('hidden');
                                    var hidden = document.getElementById('student_data');
                                    if (hidden) hidden.value = '';
                                    var text = document.getElementById('student-select-text');
                                    if (text) text.textContent = 'Начните вводить ФИО студента';
                                    var roleSel = addForm.querySelector('select[name="role"]');
                                    if (roleSel) roleSel.value = 'student';
                                    var teamSel = addForm.querySelector('select[name="team_id"]');
                                    if (teamSel) teamSel.value = '';
                                    setParticipantsError('');
                                } else {
                                    var msg = (res.data && (res.data.message || (res.data.errors ? Object.values(res.data.errors).flat()[0] : null))) || 'Не удалось добавить участника.';
                                    setParticipantsError(msg);
                                }
                            })
                            .catch(function () {
                                setParticipantsError('Не удалось добавить участника.');
                            })
                            .finally(function () {
                                if (btn) {
                                    btn.disabled = false;
                                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            });
                    });
                }

                function setTeacherError(text) {
                    var box = document.getElementById('teacher-inline-error');
                    var txt = document.getElementById('teacher-inline-error-text');
                    if (!box || !txt) return;
                    var msg = String(text || '').trim();
                    if (!msg) {
                        txt.textContent = '';
                        box.classList.add('hidden');
                        return;
                    }
                    txt.textContent = msg;
                    box.classList.remove('hidden');
                }

                var teacherForm = document.getElementById('assign-teacher-submit-form');
                if (teacherForm) {
                    teacherForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        setTeacherError('');

                        var teacherDataVal = (document.getElementById('teacher_data') || {}).value || '';
                        if (!teacherDataVal) {
                            setTeacherError('Выберите преподавателя из списка.');
                            return;
                        }

                        var btn = teacherForm.querySelector('button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.classList.add('opacity-50', 'cursor-not-allowed');
                        }

                        var fd = new FormData(teacherForm);
                        fetch(teacherForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': participantsCsrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: fd,
                        })
                            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                            .then(function (res) {
                                if (res.ok && res.data && res.data.ok) {
                                    window.competitionOnParticipantAdded(res.data);
                                    if (typeof window.syncResponsibleTeacherOrderFields === 'function') {
                                        window.syncResponsibleTeacherOrderFields(res.data.teacher_id, res.data.teacher_label);
                                    }
                                    var box = document.getElementById('assign-teacher-form');
                                    if (box) box.classList.add('hidden');
                                    var hidden = document.getElementById('teacher_data');
                                    if (hidden) hidden.value = '';
                                    var text = document.getElementById('teacher-select-text');
                                    if (text) text.textContent = 'Начните вводить ФИО преподавателя';
                                    setTeacherError('');
                                } else {
                                    var msg = (res.data && (res.data.message || (res.data.errors ? Object.values(res.data.errors).flat()[0] : null))) || 'Не удалось назначить преподавателя.';
                                    setTeacherError(msg);
                                }
                            })
                            .catch(function () {
                                setTeacherError('Не удалось назначить преподавателя.');
                            })
                            .finally(function () {
                                if (btn) {
                                    btn.disabled = false;
                                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            });
                    });
                }

                function showFlash(kind, text) {
                    var box = document.getElementById('competition-forms-flash');
                    var txt = document.getElementById('competition-forms-flash-text');
                    var icon = document.getElementById('competition-forms-flash-icon');
                    if (!box || !txt || !icon) return;

                    box.classList.remove('hidden', 'bg-green-50', 'border-green-400', 'text-green-700', 'bg-red-50', 'border-red-400', 'text-red-700');
                    if (kind === 'success') {
                        box.classList.add('bg-green-50', 'border-green-400');
                        txt.className = 'text-sm text-green-700';
                        icon.className = 'h-5 w-5 text-green-400';
                        icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                    } else {
                        box.classList.add('bg-red-50', 'border-red-400');
                        txt.className = 'text-sm text-red-700';
                        icon.className = 'h-5 w-5 text-red-400';
                        icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                    }
                    txt.textContent = text || '';
                }

                function csrf() {
                    var el = document.querySelector('meta[name="csrf-token"]');
                    return el ? el.getAttribute('content') : '';
                }

                var formsForm = document.getElementById('competition-forms-form');
                if (formsForm) {
                    var formsLocked = formsForm.getAttribute('data-forms-locked') === '1';
                    var formsReturnOnly = formsForm.getAttribute('data-forms-return-only') === '1';

                    function updateRowFormDates(row, issuedAt, submittedAt) {
                        if (!row) return;
                        var issuedDateEl = row.querySelector('.competition-form-issued-date');
                        var submittedDateEl = row.querySelector('.competition-form-submitted-date');
                        if (issuedDateEl) {
                            if (issuedAt) {
                                issuedDateEl.textContent = 'Дата выдачи: ' + issuedAt;
                                issuedDateEl.classList.remove('hidden');
                            } else {
                                issuedDateEl.textContent = '';
                                issuedDateEl.classList.add('hidden');
                            }
                        }
                        if (submittedDateEl) {
                            if (submittedAt) {
                                submittedDateEl.textContent = 'Дата сдачи: ' + submittedAt;
                                submittedDateEl.classList.remove('hidden');
                            } else {
                                submittedDateEl.textContent = '';
                                submittedDateEl.classList.add('hidden');
                            }
                        }
                    }

                    function rowFormField(row, className) {
                        return row.querySelector('[data-combobox-hidden].' + className);
                    }

                    function syncFormRowSubmittedDate(row) {
                        if (!row) return;
                        var statusField = rowFormField(row, 'competition-form-status');
                        var submittedDateEl = row.querySelector('.competition-form-submitted-date');
                        if (!statusField || !submittedDateEl) return;

                        if (row.getAttribute('data-form-return-only') === '1' || row.querySelector('.competition-form-status-wrap')) {
                            if (statusField.value === 'submitted') {
                                submittedDateEl.classList.remove('hidden');
                            } else {
                                submittedDateEl.classList.add('hidden');
                            }
                            if (row.getAttribute('data-form-return-only') === '1') return;
                        }

                        var issued = row.getAttribute('data-form-issued') === '1';
                        if (!issued || statusField.value !== 'submitted') {
                            submittedDateEl.classList.add('hidden');
                        }
                    }

                    function syncFormRowIssued(row) {
                        if (!row) return;
                        var returnOnly = row.getAttribute('data-form-return-only') === '1';
                        if (returnOnly) {
                            syncFormRowSubmittedDate(row);
                            return;
                        }

                        var issuedField = rowFormField(row, 'competition-form-issued');
                        var issued = issuedField && issuedField.value === '1';
                        row.setAttribute('data-form-issued', issued ? '1' : '0');

                        var typeWrap = row.querySelector('.competition-form-type-wrap');
                        var typeFallback = row.querySelector('.competition-form-type-fallback');
                        var statusWrap = row.querySelector('.competition-form-status-wrap');
                        var statusFallback = row.querySelector('.competition-form-status-fallback');
                        var numberInput = row.querySelector('.competition-form-number');
                        var typeField = rowFormField(row, 'competition-form-type');
                        var statusField = rowFormField(row, 'competition-form-status');

                        if (typeWrap) typeWrap.classList.toggle('hidden', !issued);
                        if (typeFallback) typeFallback.classList.toggle('hidden', issued);
                        if (statusWrap) statusWrap.classList.toggle('hidden', !issued);
                        if (statusFallback) statusFallback.classList.toggle('hidden', issued);
                        if (numberInput) numberInput.disabled = !issued;

                        if (!issued) {
                            if (typeField) {
                                typeField.value = '';
                                typeField.dispatchEvent(new Event('change', { bubbles: true }));
                                var typeRoot = typeField.closest('[data-filter-combobox]');
                                if (typeRoot && typeof typeRoot._syncFilterCombobox === 'function') {
                                    typeRoot._syncFilterCombobox();
                                }
                            }
                            if (statusField) {
                                statusField.value = 'pending';
                                var statusRoot = statusField.closest('[data-filter-combobox]');
                                if (statusRoot && typeof statusRoot._syncFilterCombobox === 'function') {
                                    statusRoot._syncFilterCombobox();
                                }
                            }
                            if (numberInput) numberInput.value = '';
                            clearRowNumberHighlight(row);
                            updateRowFormDates(row, null, null);
                        } else {
                            syncFormRowSubmittedDate(row);
                        }
                    }

                    formsForm.querySelectorAll('.competition-form-row').forEach(function (row) {
                        syncFormRowIssued(row);
                    });

                    if (typeof window.initFilterComboboxes === 'function') {
                        window.initFilterComboboxes(formsForm);
                    }

                    window.syncFormRowIssued = syncFormRowIssued;

                    function setSubmitDisabled(disabled) {
                        var btn = formsForm.querySelector('button[type=\"submit\"]');
                        if (!btn) return;
                        btn.disabled = !!disabled;
                        if (disabled) {
                            btn.classList.add('opacity-50', 'cursor-not-allowed');
                        } else {
                            btn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }

                    function normalizeNumber(v) {
                        return String(v || '').trim();
                    }

                    function rowTypeNumberKey(row, onlyPending) {
                        if (!row) return null;
                        if (row.getAttribute('data-form-issued') !== '1') return null;
                        var statusField = rowFormField(row, 'competition-form-status');
                        if (onlyPending && (!statusField || statusField.value !== 'pending')) return null;
                        var typeField = rowFormField(row, 'competition-form-type');
                        var input = row.querySelector('input[name$=\"[form_number]\"]');
                        var typeId = typeField ? String(typeField.value || '').trim() : '';
                        var num = input ? normalizeNumber(input.value) : '';
                        if (!typeId || !num) return null;
                        return typeId + ':' + num;
                    }

                    function clearRowNumberHighlight(row) {
                        if (!row) return;
                        var input = row.querySelector('input[name$=\"[form_number]\"]');
                        var typeField = rowFormField(row, 'competition-form-type');
                        var typeTrigger = typeField ? typeField.closest('[data-filter-combobox]')?.querySelector('[data-combobox-trigger]') : null;
                        [input, typeTrigger].forEach(function (el) {
                            if (!el) return;
                            el.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                            el.removeAttribute('aria-invalid');
                        });
                    }

                    function validateUniqueNumbersLive() {
                        var rows = formsForm.querySelectorAll('tbody tr');
                        var byKey = {};
                        var duplicates = {};

                        rows.forEach(function (row) {
                            clearRowNumberHighlight(row);
                            var key = rowTypeNumberKey(row, true);
                            if (!key) return;
                            if (!byKey[key]) byKey[key] = [];
                            byKey[key].push(row);
                        });

                        rows.forEach(function (row) {
                            var statusField = rowFormField(row, 'competition-form-status');
                            if (!statusField || statusField.value === 'pending') return;
                            var key = rowTypeNumberKey(row, false);
                            if (!key || !byKey[key] || byKey[key].length < 1) return;
                            if (!duplicates[key]) duplicates[key] = [];
                            if (duplicates[key].indexOf(row) === -1) duplicates[key].push(row);
                            byKey[key].forEach(function (r) {
                                if (duplicates[key].indexOf(r) === -1) duplicates[key].push(r);
                            });
                        });

                        Object.keys(byKey).forEach(function (key) {
                            if (byKey[key].length > 1) {
                                duplicates[key] = byKey[key];
                            }
                        });

                        var dupKeys = Object.keys(duplicates);
                        if (dupKeys.length) {
                            dupKeys.forEach(function (key) {
                                duplicates[key].forEach(function (row) {
                                    var input = row.querySelector('input[name$=\"[form_number]\"]');
                                    var typeField = rowFormField(row, 'competition-form-type');
                                    var typeTrigger = typeField ? typeField.closest('[data-filter-combobox]')?.querySelector('[data-combobox-trigger]') : null;
                                    [input, typeTrigger].forEach(function (el) {
                                        if (!el) return;
                                        el.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                                        el.setAttribute('aria-invalid', 'true');
                                    });
                                });
                            });
                            setSubmitDisabled(true);
                            showFlash('error', 'Этот вид формы и номер уже заняты другим студентом (статус «Не сдал»)');
                            return false;
                        }

                        setSubmitDisabled(false);
                        return true;
                    }

                    formsForm.addEventListener('input', function (e) {
                        var t = e.target;
                        if (t && t.name && t.name.indexOf('[form_number]') !== -1) {
                            validateUniqueNumbersLive();
                        }
                    });

                    formsForm.addEventListener('change', function (e) {
                        var t = e.target;
                        if (!t || !t.classList) return;
                        var row = t.closest('.competition-form-row');
                        if (!row) return;

                        if (t.classList.contains('competition-form-status')) {
                            syncFormRowSubmittedDate(row);
                            if (!formsReturnOnly) validateUniqueNumbersLive();
                            return;
                        }
                        if (t.classList.contains('competition-form-issued')) {
                            if (!formsReturnOnly) {
                                syncFormRowIssued(row);
                                validateUniqueNumbersLive();
                            }
                            return;
                        }
                        if (t.classList.contains('competition-form-type')) {
                            validateUniqueNumbersLive();
                        }
                    });

                    formsForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (formsLocked) return;
                        if (!formsReturnOnly && !validateUniqueNumbersLive()) return;
                        showFlash('success', 'Сохраняем…');

                        var fd = new FormData(formsForm);
                        fetch(formsForm.action, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                            },
                        })
                            .then(function (res) {
                                return res.json().then(function (data) {
                                    return { ok: res.ok, status: res.status, data: data };
                                });
                            })
                            .then(function (r) {
                                if (r.ok && r.data && r.data.ok) {
                                    showFlash('success', r.data.message || 'Сохранено.');
                                    if (r.data.forms) {
                                        Object.keys(r.data.forms).forEach(function (userId) {
                                            var row = formsForm.querySelector('.competition-form-row[data-user-id=\"' + userId + '\"]');
                                            if (!row) return;
                                            var dates = r.data.forms[userId] || {};
                                            updateRowFormDates(row, dates.issued_at || null, dates.submitted_at || null);
                                        });
                                    }
                                } else {
                                    var msg = (r.data && (r.data.message || (r.data.errors ? Object.values(r.data.errors)[0][0] : null))) || 'Ошибка сохранения.';
                                    showFlash('error', msg);
                                }
                            })
                            .catch(function () {
                                showFlash('error', 'Ошибка сохранения.');
                            });
                    });

                    if (!formsReturnOnly) {
                        validateUniqueNumbersLive();
                    }
                }

                var typeForm = document.getElementById('competition-form-types-form');
                if (typeForm) {
                    typeForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        var fd2 = new FormData(typeForm);
                        fetch(typeForm.action, {
                            method: 'POST',
                            body: fd2,
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                            },
                        })
                            .then(function (res) {
                                return res.json().then(function (data) {
                                    return { ok: res.ok, data: data };
                                });
                            })
                            .then(function (r) {
                                if (r.ok && r.data && r.data.ok && r.data.type) {
                                    // Добавляем новый тип во все селекты
                                    var opt = document.createElement('option');
                                    opt.value = String(r.data.type.id);
                                    opt.textContent = String(r.data.type.name);
                                    document.querySelectorAll('select[name$="[form_type_id]"]').forEach(function (sel) {
                                        sel.appendChild(opt.cloneNode(true));
                                    });
                                    var input = document.getElementById('new_form_type_name');
                                    if (input) input.value = '';
                                    var box = document.getElementById('create-form-type');
                                    if (box) box.classList.add('hidden');
                                    showFlash('success', r.data.message || 'Вид формы создан.');
                                } else {
                                    var msg = (r.data && (r.data.message || (r.data.errors ? Object.values(r.data.errors)[0][0] : null))) || 'Ошибка создания вида формы.';
                                    showFlash('error', msg);
                                }
                            })
                            .catch(function () {
                                showFlash('error', 'Ошибка создания вида формы.');
                            });
                    });
                }
            })();
        </script>

        <script>
            (function () {
                function showAdmissionFlash(kind, text) {
                    var box = document.getElementById('competition-admission-flash');
                    var txt = document.getElementById('competition-admission-flash-text');
                    var icon = document.getElementById('competition-admission-flash-icon');
                    if (!box || !txt || !icon) return;

                    box.classList.remove('hidden', 'bg-green-50', 'border-green-400', 'text-green-700', 'bg-red-50', 'border-red-400', 'text-red-700');
                    if (kind === 'success') {
                        box.classList.add('bg-green-50', 'border-green-400');
                        txt.className = 'text-sm text-green-700';
                        icon.className = 'h-5 w-5 text-green-400';
                        icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                    } else {
                        box.classList.add('bg-red-50', 'border-red-400');
                        txt.className = 'text-sm text-red-700';
                        icon.className = 'h-5 w-5 text-red-400';
                        icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                    }
                    txt.textContent = text || '';
                }

                function csrfToken() {
                    var el = document.querySelector('meta[name="csrf-token"]');
                    return el ? el.getAttribute('content') : '';
                }

                var admissionForm = document.getElementById('competition-admission-form');
                if (admissionForm) {
                    if (typeof window.initFilterComboboxes === 'function') {
                        window.initFilterComboboxes(admissionForm);
                    }

                    function setDocumentError(text) {
                        var box = document.getElementById('medical-admission-document-error');
                        if (!box) return;
                        var msg = String(text || '').trim();
                        if (!msg) {
                            box.textContent = '';
                            box.classList.add('hidden');
                            return;
                        }
                        box.textContent = msg;
                        box.classList.remove('hidden');
                    }

                    function hideAdmissionFlash() {
                        var box = document.getElementById('competition-admission-flash');
                        if (!box) return;
                        box.classList.add('hidden');
                    }

                    function setFileState(hasFile) {
                        var state = document.getElementById('medical-admission-file-state');
                        if (!state) return;
                        state.textContent = hasFile ? 'Файл выбран' : 'Файл не выбран';
                    }

                    function clearFileInput() {
                        var input = admissionForm.querySelector('input[name="medical_admission_document"]');
                        if (!input) return;
                        input.value = '';
                        setFileState(false);
                    }

                    function updateOpenLink(url) {
                        var link = document.getElementById('medical-admission-open-link');
                        var currentText = document.getElementById('medical-admission-current-file-text');
                        if (!link) return;
                        if (!url) {
                            link.classList.add('hidden');
                            link.setAttribute('href', '#');
                            if (currentText) currentText.innerHTML = '<span class="font-medium">Текущий файл:</span> не прикреплён';
                            return;
                        }
                        link.setAttribute('href', url);
                        link.classList.remove('hidden');
                        if (currentText) currentText.innerHTML = '<span class="font-medium">Текущий файл:</span> прикреплён';
                    }

                    var pickBtn = document.getElementById('medical-admission-pick-file');
                    var fileInputEl = admissionForm.querySelector('input[name="medical_admission_document"]');
                    if (pickBtn && fileInputEl) {
                        pickBtn.addEventListener('click', function () {
                            fileInputEl.click();
                        });
                        fileInputEl.addEventListener('change', function () {
                            setFileState(!!(fileInputEl.files && fileInputEl.files.length > 0));
                            setDocumentError('');
                        });
                    }

                    admissionForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        var submitter = e.submitter;
                        var action = submitter && submitter.value ? String(submitter.value) : 'save_admissions';
                        var btn = submitter && submitter.tagName === 'BUTTON' ? submitter : admissionForm.querySelector('button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.classList.add('opacity-50', 'cursor-not-allowed');
                        }

                        setDocumentError('');
                        hideAdmissionFlash();
                        if (action === 'attach_document') {
                            var fileInput = admissionForm.querySelector('input[name="medical_admission_document"]');
                            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                                setDocumentError('Выберите файл.');
                                if (btn) {
                                    btn.disabled = false;
                                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                                return;
                            }
                        }
                        var fd = new FormData(admissionForm);
                        fd.set('submit_action', action);
                        fetch(admissionForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: fd,
                        })
                            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                            .then(function (res) {
                                if (res.ok && res.data && res.data.ok) {
                                    setDocumentError('');
                                    showAdmissionFlash('success', res.data.message || 'Допуск к соревнованию сохранён.');
                                    if (action === 'attach_document' && res.data.medical_admission_document) {
                                        updateOpenLink(res.data.medical_admission_document.url);
                                        clearFileInput();
                                    }
                                } else {
                                    var docErr = null;
                                    if (res.data && res.data.errors && res.data.errors.medical_admission_document && res.data.errors.medical_admission_document[0]) {
                                        docErr = res.data.errors.medical_admission_document[0];
                                    }
                                    if (docErr) {
                                        setDocumentError(docErr);
                                        return;
                                    }

                                    var msg = (res.data && (res.data.message || (res.data.errors ? Object.values(res.data.errors)[0][0] : null))) || 'Не удалось сохранить допуск.';
                                    if (action === 'attach_document') {
                                        setDocumentError(msg);
                                        return;
                                    }
                                    showAdmissionFlash('error', msg);
                                }
                            })
                            .catch(function () {
                                setDocumentError('');
                                showAdmissionFlash('error', 'Не удалось сохранить допуск.');
                            })
                            .finally(function () {
                                if (btn) {
                                    btn.disabled = false;
                                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            });
                    });
                }
            })();
        </script>

        <script>
            function switchCompetitionTeacherTab(tabName) {
                var tabKey = 'competitionTeacherTab:{{ $competition->id }}';
                try {
                    localStorage.setItem(tabKey, tabName);
                } catch (e) {
                    // ignore
                }

                document.querySelectorAll('#content-participants, #content-forms, #content-admission').forEach(content => {
                    content.classList.add('hidden');
                });

                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('active', 'text-blue-600', 'border-blue-600');
                    button.classList.add('text-gray-500', 'border-transparent');
                });

                const activeContent = document.getElementById('content-' + tabName);
                if (activeContent) activeContent.classList.remove('hidden');

                const activeButton = document.getElementById('tab-' + tabName);
                if (activeButton) {
                    activeButton.classList.add('active', 'text-blue-600', 'border-blue-600');
                    activeButton.classList.remove('text-gray-500', 'border-transparent');
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var params = new URLSearchParams(window.location.search);
                var hash = (window.location.hash || '').replace(/^#/, '');
                var tabFromUrl = params.get('tab');

                if (tabFromUrl === 'participants' || hash === 'participants' || hash === 'participants-table-wrap') {
                    switchCompetitionTeacherTab('participants');
                    window.setTimeout(function () {
                        var target = document.getElementById('participants-table-wrap');
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 50);

                    return;
                }

                var tabKey = 'competitionTeacherTab:{{ $competition->id }}';
                var savedTab = null;
                try {
                    savedTab = localStorage.getItem(tabKey);
                } catch (e) {
                    savedTab = null;
                }

                if (savedTab === 'forms' || savedTab === 'participants' || savedTab === 'admission') {
                    switchCompetitionTeacherTab(savedTab);
                }
            });
        </script>

        <!-- Приказы и отчёты -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Приказы и отчёты</h2>
            
            @if(auth()->user()->role === 'teacher')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Кнопка для первого приказа: Об освобождении от учебных занятий -->
                    <button 
                        onclick="showOrderForm(1)"
                        class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center"
                    >
                        <div class="font-semibold mb-1">Приказ №1</div>
                        <div class="text-sm">Об освобождении от учебных занятий</div>
                    </button>

                    <!-- Кнопка для второго приказа: Об участии в мероприятии -->
                    <button 
                        onclick="showOrderForm(2)"
                        class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center"
                    >
                        <div class="font-semibold mb-1">Приказ №2</div>
                        <div class="text-sm">Об участии в мероприятии</div>
                    </button>

                    <!-- Кнопка для третьего приказа: О месте проведения занятий -->
                    <button 
                        onclick="showOrderForm(3)"
                        class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-center"
                    >
                        <div class="font-semibold mb-1">Приказ №3</div>
                        <div class="text-sm">О месте проведения занятий</div>
                    </button>

                    <button
                        onclick="showOrderForm(4)"
                        class="px-4 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-center"
                    >
                        <div class="font-semibold mb-1">Именная заявка</div>
                        <div class="text-sm">На участие в соревновании</div>
                    </button>
                </div>

                <!-- Форма для первого приказа: Об освобождении от учебных занятий -->
                <div id="order-1-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 overflow-visible">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Приказ об освобождении от учебных занятий</h3>
                    <form action="{{ route('competitions.generate-order-1', $competition) }}" method="POST" class="space-y-4 overflow-visible">
                        @csrf
                        
                        <div>
                            <label for="order1_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Дата приказа <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="order1_date" 
                                name="order_date" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                value="{{ now()->format('Y-m-d') }}"
                            >
                        </div>

                        <div class="relative overflow-visible">
                            <label for="accompanying_teacher" class="block text-sm font-medium text-gray-700 mb-2">
                                Преподаватель (ответственный за сопровождение) <span class="text-red-500">*</span>
                            </label>
                            @php
                                $teacherUser = $competition->teacher?->user;
                                $selectedTeacherId = old('accompanying_teacher', $teacherUser?->id ?? '');
                            @endphp
                            <input type="hidden" name="accompanying_teacher" id="accompanying_teacher" value="{{ $selectedTeacherId }}" required>
                            <div class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700">
                                <span data-responsible-teacher-label>
                                    {{ $teacherUser ? ($teacherUser->lastname.' '.$teacherUser->firstname.' '.($teacherUser->patronymic ?? '')) : 'Преподаватель не назначен' }}
                                </span>
                            </div>
                            @error('accompanying_teacher')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="dispatcher" class="block text-sm font-medium text-gray-700 mb-2">
                                Диспетчер <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="dispatcher" 
                                name="dispatcher" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Например: Мелентьева В.Д."
                            >
                        </div>

                        <div>
                            <label for="deputy_director_1" class="block text-sm font-medium text-gray-700 mb-2">
                                Заместитель директора <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="deputy_director_1" 
                                name="deputy_director" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Например: Богачева М.А."
                            >
                        </div>

                        <div>
                            <label for="director_name_1" class="block text-sm font-medium text-gray-700 mb-2">
                                Директор (подпись)
                            </label>
                            <input 
                                type="text" 
                                id="director_name_1" 
                                name="director_name" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50"
                                placeholder="Например: А.Н. Якубовский"
                                value="А.Н. Якубовский"
                                readonly
                            >
                            <p class="mt-1 text-sm text-gray-500">ФИО директора фиксированное</p>
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button 
                                type="button"
                                onclick="showOrderForm(0)"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Сгенерировать приказ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Форма для второго приказа: Об участии в мероприятии -->
                <div id="order-2-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200 overflow-visible">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Приказ об участии в мероприятии</h3>
                    <form action="{{ route('competitions.generate-order-2', $competition) }}" method="POST" class="space-y-4 overflow-visible">
                        @csrf
                        
                        <div>
                            <label for="order2_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Дата приказа <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="order2_date" 
                                name="order_date" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                value="{{ now()->format('Y-m-d') }}"
                            >
                        </div>

                        <div class="relative overflow-visible">
                            <label for="teacher_participant_2" class="block text-sm font-medium text-gray-700 mb-2">
                                Преподаватель (сопровождающий) <span class="text-red-500">*</span>
                            </label>
                            @php
                                $teacherUser2 = $competition->teacher?->user;
                                $selectedTeacherId2 = old('teacher_participant', $teacherUser2?->id ?? '');
                            @endphp
                            <input type="hidden" name="teacher_participant" id="teacher_participant_2" value="{{ $selectedTeacherId2 }}" required>
                            <div class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700">
                                <span data-responsible-teacher-label>
                                    {{ $teacherUser2 ? ($teacherUser2->lastname.' '.$teacherUser2->firstname.' '.($teacherUser2->patronymic ?? '')) : 'Преподаватель не назначен' }}
                                </span>
                            </div>
                            @error('teacher_participant')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="head_of_studies_2" class="block text-sm font-medium text-gray-700 mb-2">
                                Заведующая учебной частью <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="head_of_studies_2" 
                                name="head_of_studies" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Например: Филиппова Т.Ф."
                            >
                        </div>

                        <div>
                            <label for="deputy_director_2" class="block text-sm font-medium text-gray-700 mb-2">
                                Заместитель директора <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="deputy_director_2" 
                                name="deputy_director" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Например: Богачева М.А."
                            >
                        </div>

                        <div>
                            <label for="director_name_2" class="block text-sm font-medium text-gray-700 mb-2">
                                Директор (подпись) <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="director_name_2" 
                                name="director_name" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Например: А.Н. Якубовский"
                            >
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button 
                                type="button"
                                onclick="showOrderForm(0)"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                            >
                                Сгенерировать приказ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Форма для третьего приказа: О месте проведения занятий -->
                <div id="order-3-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Приказ о месте проведения занятий по дисциплине «Физическая культура»</h3>
                    <form action="{{ route('competitions.generate-order-3', $competition) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="order3_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Дата приказа <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="order3_date" 
                                name="order_date" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                value="{{ now()->format('Y-m-d') }}"
                            >
                        </div>

                        <div>
                            <label for="location_classes" class="block text-sm font-medium text-gray-700 mb-2">
                                Место проведения занятий <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="location_classes" 
                                name="location_classes" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Например: о. Юность"
                            >
                        </div>


                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button 
                                type="button"
                                onclick="showOrderForm(0)"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                            >
                                Сгенерировать приказ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Именная заявка -->
                <div id="order-4-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Именная заявка</h3>
                    <p class="mb-4 text-sm text-gray-600">
                        В документ подставляются название соревнования, вид спорта, учреждение «Иркутского авиационного техникума» и список студентов-участников.
                    </p>
                    <form action="{{ route('competitions.generate-named-application', $competition) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="application_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Дата документа
                            </label>
                            <input
                                type="date"
                                id="application_date"
                                name="application_date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                value="{{ now()->format('Y-m-d') }}"
                            >
                        </div>
                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button
                                type="button"
                                onclick="showOrderForm(0)"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition"
                            >
                                Скачать PDF
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Результаты соревнования -->
        @php
            $resultsType = $competition->result_type ?? 'team';
            $hasCompetitionResult = $competition->results
                ->when($resultsType === 'personal', fn ($c) => $c->where('result_type', 'personal'))
                ->when($resultsType !== 'personal', fn ($c) => $c->where('result_type', 'team'))
                ->contains(fn ($r) => filled(trim((string) ($r->place ?? ''))));
        @endphp
        <div
            id="competition-results"
            class="bg-white rounded-lg shadow-md p-6"
            data-competition-id="{{ $competition->id }}"
            data-results-type="{{ $resultsType }}"
            data-has-result="{{ $hasCompetitionResult ? '1' : '0' }}"
        >
            <div id="competition-results-feedback" class="hidden mb-4" role="status"></div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Результаты соревнования</h2>
                @if($resultsType === 'team' && ($competition->status === 'finished' || $competition->status === 'ongoing') && ! $hasCompetitionResult)
                    <button
                        type="button"
                        id="add-result-toggle"
                        onclick="document.getElementById('add-result-form').classList.toggle('hidden')"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium"
                    >
                        Добавить результат
                    </button>
                @endif
            </div>

            @if($competition->status !== 'finished' && $competition->status !== 'ongoing')
                <div class="text-gray-500 text-sm"></div>
            @else
                @if($resultsType === 'personal')
                    @php
                        $resultByUserId = $competition->results->where('result_type', 'personal')->keyBy('user_id');
                        $studentParticipantsForResults = $competition->participants->filter(fn ($p) => ($p->role ?? 'student') === 'student');
                    @endphp

                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        Личное соревнование: место указывается для каждого студента отдельно.
                    </div>

                    @php
                        $resultPlaceErrors = collect($errors->getMessages())
                            ->filter(fn ($messages, $key) => $key === 'results' || str_starts_with((string) $key, 'results.'))
                            ->flatten();
                    @endphp
                    @if($resultPlaceErrors->isNotEmpty())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            @foreach($resultPlaceErrors as $message)
                                <p>{{ $message }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form
                        id="personal-results-form"
                        action="{{ route('competitions.results.store', $competition) }}"
                        method="POST"
                        class="space-y-4"
                    >
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Студент</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Команда</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вид спорта</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($studentParticipantsForResults as $p)
                                        @php
                                            $u = $p->user;
                                            $uid = (int) $p->user_id;
                                            $r = $resultByUserId->get($uid);
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3 text-sm text-gray-900">{{ $u->lastname }} {{ $u->firstname }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-900">{{ $p->team?->name ?? '—' }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-900">{{ $p->team?->sport?->name ?? '—' }}</td>
                                            <td class="px-3 py-3">
                                                <input
                                                    type="number"
                                                    name="results[{{ $uid }}]"
                                                    value="{{ old('results.'.$uid, $r?->place ?? '') }}"
                                                    min="1"
                                                    step="1"
                                                    class="personal-result-place-input w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                    placeholder="Например: 1"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Сохранить результаты
                            </button>
                        </div>
                    </form>
                @else
                <!-- Форма добавления результата -->
                <div id="add-result-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Добавить результат</h3>
                    <form id="add-result-form-submit" action="{{ route('competitions.results.store', $competition) }}" method="POST">
                        @csrf
                        @if(!$competition->team)
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-600">У выбранного вида спорта нет команды. Невозможно добавить результат.</p>
                            </div>
                        @else
                            <input type="hidden" name="team_id" value="{{ $competition->team->id }}">
                        @endif
                        <div class="mb-4">
                            <label for="place" class="block text-sm font-medium text-gray-700 mb-2">
                                Место <span class="text-red-500">*</span>
                            </label>
                            <div class="relative overflow-visible">
                                <!-- Скрытое поле для формы -->
                                <input type="hidden" name="place" id="place" value="{{ old('place') }}" required>
                                
                                <!-- Кнопка выбора места -->
                                <button 
                                    type="button"
                                    id="place-select-button"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('place') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                                    onclick="togglePlaceDropdown()"
                                >
                                    <span id="place-select-text" class="text-gray-700">
                                        {{ old('place') ?: 'Выберите место' }}
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Dropdown меню -->
                                <div id="place-dropdown" class="hidden absolute z-50 w-full mt-1 max-h-60 overflow-y-auto bg-white border border-gray-300 rounded-lg shadow-lg">
                                    <div id="place-list">
                                        @php
                                            $placeOptions = collect(range(1, 10))->map(fn ($n) => (string) $n)->all();
                                        @endphp
                                        @foreach($placeOptions as $placeOption)
                                            <div 
                                                class="place-option px-4 py-2 hover:bg-blue-50 cursor-pointer {{ old('place') == $placeOption ? 'bg-blue-100' : '' }}"
                                                data-place="{{ $placeOption }}"
                                                onclick="selectPlace('{{ addslashes($placeOption) }}')"
                                            >
                                                <div class="font-medium text-gray-900">{{ $placeOption }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('place')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button 
                                type="button"
                                onclick="document.getElementById('add-result-form').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                            >
                                Добавить результат
                            </button>
                        </div>
                    </form>
                </div>

                <div id="competition-results-list">
                @php
                    $displayCompetitionResults = \App\Support\CompetitionResultPage::sortedResultsForListing($competition);
                @endphp
                @if($displayCompetitionResults->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="results-listing-table min-w-full border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if($resultsType === 'personal')
                                        <th class="border-b border-gray-300 px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Соревнование</th>
                                    @endif
                                    <th class="border-b border-gray-300 px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                    @if($resultsType === 'personal')
                                        <th class="border-b border-gray-300 px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участник</th>
                                        <th class="border-b border-gray-300 px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вид спорта</th>
                                    @else
                                        <th class="border-b border-gray-300 px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                                    @endif
                                    <th class="border-b border-gray-300 px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody id="competition-results-tbody" class="bg-white">
                                @foreach($displayCompetitionResults as $result)
                                    @include('competitions.partials.competition-show-result-row', [
                                        'competition' => $competition,
                                        'result' => $result,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p id="competition-results-empty" class="text-gray-500 text-sm">Результаты пока не добавлены. Используйте кнопку «Добавить результат» выше.</p>
                @endif
                </div>
            @endif
        </div>
                @endif

        <!-- Модальное окно редактирования результата -->
        <div id="edit-result-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Редактировать результат</h3>
                    <form id="edit-result-form">
                        @csrf
                        <input type="hidden" id="edit-result-id" name="result_id">
                        <div class="mb-4">
                            <label for="edit-result-place" class="block text-sm font-medium text-gray-700 mb-2">
                                Место <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="edit-result-place" 
                                name="place" 
                                required
                                maxlength="45"
                                placeholder="Например: 1, 2 или 3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <p id="edit-result-error" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button 
                                type="button"
                                onclick="closeEditResultModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Действия -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Действия</h2>
            <div class="flex flex-wrap gap-3">
                <a 
                    href="{{ route('competitions.edit', $competition) }}" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Редактировать
                </a>
                
                @if($competition->status !== 'ongoing' && $competition->status !== 'cancelled' && $competition->status !== 'finished')
                    <form 
                        action="{{ route('competitions.cancel', $competition) }}" 
                        method="POST" 
                        class="inline"
                        onsubmit="return confirm('Вы уверены, что хотите отменить это соревнование?')"
                    >
                        @csrf
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition"
                        >
                            Отменить соревнование
                        </button>
                    </form>
                @endif
                
                @if($competition->status !== 'finished')
                    <form 
                        action="{{ route('competitions.destroy', $competition) }}" 
                        method="POST" 
                        class="inline"
                        onsubmit="return confirm('Вы уверены, что хотите удалить это соревнование?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                        >
                            Удалить
                        </button>
                    </form>
                @endif
                
                <a 
                    href="{{ $teacherCompetitionListBackUrl }}" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    {{ $teacherCompetitionBackLabel }}
                </a>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;
        let currentSearchRequest = null;
        let activeSearchTerm = '';

        function toggleStudentDropdown() {
            const dropdown = document.getElementById('student-dropdown');
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    const searchInput = document.getElementById('student-search');
                    if (searchInput) searchInput.focus();
                }, 100);
            }
        }

        function filterStudents(searchTerm) {
            const search = searchTerm.trim();
            const studentList = document.getElementById('student-list');
            const studentLoading = document.getElementById('student-loading');
            const studentNoResults = document.getElementById('student-no-results');
            const studentInitialMessage = document.getElementById('student-initial-message');
            
            clearTimeout(searchTimeout);
            
            if (currentSearchRequest) {
                currentSearchRequest.abort();
                currentSearchRequest = null;
            }
            
            if (search === '' || search.length < 2) {
                studentList.innerHTML = '';
                if (studentLoading) studentLoading.classList.add('hidden');
                if (studentNoResults) studentNoResults.classList.add('hidden');
                if (studentInitialMessage) studentInitialMessage.classList.remove('hidden');
                activeSearchTerm = '';
                return;
            }
            
            activeSearchTerm = search;
            
            studentList.innerHTML = '';
            if (studentInitialMessage) studentInitialMessage.classList.add('hidden');
            if (studentLoading) studentLoading.classList.remove('hidden');
            if (studentNoResults) studentNoResults.classList.add('hidden');
            
            searchTimeout = setTimeout(() => {
                if (activeSearchTerm !== search) {
                    return;
                }
                
                const xhr = new XMLHttpRequest();
                const requestSearchTerm = search;
                currentSearchRequest = xhr;
                
                const url = '{{ route("competitions.search-students", $competition, false) }}?search=' + encodeURIComponent(search);
                
                xhr.open('GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                
                xhr.onload = function() {
                    if (activeSearchTerm !== requestSearchTerm || currentSearchRequest !== xhr) {
                        return;
                    }
                    
                    currentSearchRequest = null;
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (activeSearchTerm === requestSearchTerm) {
                                displayStudents(response.students || []);
                            }
                        } catch (e) {
                            studentList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка обработки данных</div>';
                            if (studentLoading) studentLoading.classList.add('hidden');
                        }
                    } else {
                        studentList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка ' + xhr.status + '</div>';
                        if (studentLoading) studentLoading.classList.add('hidden');
                    }
                };
                
                xhr.onerror = function() {
                    if (activeSearchTerm === requestSearchTerm && currentSearchRequest === xhr) {
                        studentList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка соединения</div>';
                        if (studentLoading) studentLoading.classList.add('hidden');
                        currentSearchRequest = null;
                    }
                };
                
                xhr.onabort = function() {
                    if (currentSearchRequest === xhr) {
                        currentSearchRequest = null;
                    }
                };
                
                xhr.send();
            }, 200);
        }

        function toggleTeacherDropdown() {
            const dropdown = document.getElementById('teacher-dropdown');
            dropdown.classList.toggle('hidden');

            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    const searchInput = document.getElementById('teacher-search');
                    if (searchInput) searchInput.focus();
                }, 100);
            }
        }

        let teacherSearchTimeout = null;
        let currentTeacherSearchRequest = null;
        let activeTeacherSearchTerm = '';

        function filterTeachers(searchTerm) {
            const search = (searchTerm || '').trim();
            const teacherList = document.getElementById('teacher-list');
            const teacherNoResults = document.getElementById('teacher-no-results');
            const teacherLoading = document.getElementById('teacher-loading');
            const teacherInitialMessage = document.getElementById('teacher-initial-message');

            if (!teacherList) return;

            if (teacherSearchTimeout) {
                clearTimeout(teacherSearchTimeout);
                teacherSearchTimeout = null;
            }

            if (currentTeacherSearchRequest) {
                currentTeacherSearchRequest.abort();
                currentTeacherSearchRequest = null;
            }

            if (search === '' || search.length < 2) {
                teacherList.innerHTML = '';
                if (teacherLoading) teacherLoading.classList.add('hidden');
                if (teacherNoResults) teacherNoResults.classList.add('hidden');
                if (teacherInitialMessage) teacherInitialMessage.classList.remove('hidden');
                activeTeacherSearchTerm = '';
                return;
            }

            activeTeacherSearchTerm = search;
            teacherList.innerHTML = '';
            if (teacherInitialMessage) teacherInitialMessage.classList.add('hidden');
            if (teacherLoading) teacherLoading.classList.remove('hidden');
            if (teacherNoResults) teacherNoResults.classList.add('hidden');

            teacherSearchTimeout = setTimeout(() => {
                if (activeTeacherSearchTerm !== search) return;

                const xhr = new XMLHttpRequest();
                const requestSearchTerm = search;
                currentTeacherSearchRequest = xhr;
                const url = '{{ route("competitions.search-teachers", $competition, false) }}?search=' + encodeURIComponent(search);

                xhr.open('GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.onload = function() {
                    if (activeTeacherSearchTerm !== requestSearchTerm || currentTeacherSearchRequest !== xhr) return;
                    currentTeacherSearchRequest = null;

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            displayTeachers(response.teachers || []);
                        } catch (e) {
                            teacherList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка обработки данных</div>';
                            if (teacherLoading) teacherLoading.classList.add('hidden');
                        }
                    } else {
                        teacherList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка ' + xhr.status + '</div>';
                        if (teacherLoading) teacherLoading.classList.add('hidden');
                    }
                };

                xhr.onerror = function() {
                    if (activeTeacherSearchTerm === requestSearchTerm && currentTeacherSearchRequest === xhr) {
                        teacherList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка соединения</div>';
                        if (teacherLoading) teacherLoading.classList.add('hidden');
                        currentTeacherSearchRequest = null;
                    }
                };

                xhr.onabort = function() {
                    if (currentTeacherSearchRequest === xhr) {
                        currentTeacherSearchRequest = null;
                    }
                };

                xhr.send();
            }, 200);
        }

        function displayTeachers(teachers) {
            const teacherList = document.getElementById('teacher-list');
            const teacherNoResults = document.getElementById('teacher-no-results');
            const teacherLoading = document.getElementById('teacher-loading');

            if (!teacherList) return;

            teacherList.innerHTML = '';
            if (teacherLoading) teacherLoading.classList.add('hidden');

            if (!teachers || teachers.length === 0) {
                if (teacherNoResults) teacherNoResults.classList.remove('hidden');
                return;
            }
            if (teacherNoResults) teacherNoResults.classList.add('hidden');

            teachers.forEach(teacher => {
                const option = document.createElement('div');
                option.className = 'teacher-option px-4 py-2 hover:bg-blue-50 cursor-pointer';
                option.dataset.teacher = JSON.stringify(teacher);
                option.addEventListener('click', function() {
                    selectTeacher(option);
                });

                let html = '<div class="font-medium text-gray-900">';
                html += (teacher.lastname || '') + ' ' + (teacher.firstname || '') + ' ' + (teacher.patronymic || '');
                html += '</div>';
                html += '<div class="text-sm text-gray-500">' + (teacher.login || '') + '</div>';

                option.innerHTML = html;
                teacherList.appendChild(option);
            });
        }

        function selectTeacher(optionElement) {
            if (!optionElement) return;
            const teacherData = optionElement.dataset.teacher;
            if (!teacherData) return;

            const teacherDataInput = document.getElementById('teacher_data');
            const teacherText = document.getElementById('teacher-select-text');
            const dropdown = document.getElementById('teacher-dropdown');

            teacherDataInput.value = teacherData;

            try {
                const parsed = JSON.parse(teacherData);
                const text = `${parsed.lastname ?? ''} ${parsed.firstname ?? ''} ${parsed.patronymic ?? ''} (${parsed.login ?? ''})`.trim();
                teacherText.textContent = text;
            } catch (e) {}

            if (dropdown) dropdown.classList.add('hidden');
        }

        function displayStudents(students) {
            const studentList = document.getElementById('student-list');
            const studentNoResults = document.getElementById('student-no-results');
            const studentLoading = document.getElementById('student-loading');
            
            studentList.innerHTML = '';
            
            if (studentLoading) {
                studentLoading.classList.add('hidden');
            }
            
            if (!students || students.length === 0) {
                if (studentNoResults) studentNoResults.classList.remove('hidden');
                return;
            }
            
            if (studentNoResults) studentNoResults.classList.add('hidden');
            
            students.forEach(student => {
                const option = document.createElement('div');
                option.className = 'student-option px-4 py-2 hover:bg-blue-50 cursor-pointer';
                option.dataset.student = JSON.stringify(student);
                option.addEventListener('click', function() {
                    selectStudent(option);
                });
                
                let html = '<div class="font-medium text-gray-900">';
                html += (student.lastname || '') + ' ' + (student.firstname || '') + ' ' + (student.patronymic || '');
                if (student.status_fizorg) {
                    html += ' <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">[Физорг]</span>';
                }
                html += '</div>';
                html += '<div class="text-sm text-gray-500">' + (student.login || '') + '</div>';
                
                option.innerHTML = html;
                studentList.appendChild(option);
            });
        }

        function selectStudent(optionElement) {
            if (!optionElement) return;
            const studentData = optionElement.dataset.student;
            if (!studentData) return;
            
            const studentDataInput = document.getElementById('student_data');
            const studentText = document.getElementById('student-select-text');
            const dropdown = document.getElementById('student-dropdown');
            
            studentDataInput.value = studentData;
            
            try {
                const parsed = JSON.parse(studentData);
                const text = `${parsed.lastname ?? ''} ${parsed.firstname ?? ''} ${parsed.patronymic ?? ''} (${parsed.login ?? ''})`.trim();
                studentText.textContent = text;
            } catch (e) {
                console.error('Ошибка парсинга student_data:', e);
            }
            
            if (dropdown) dropdown.classList.add('hidden');
        }


        function toggleAccompanyingTeacherDropdown() {
            const dropdown = document.getElementById('accompanying-teacher-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        function selectAccompanyingTeacher(value, label) {
            const teacherInput = document.getElementById('accompanying_teacher');
            const teacherText = document.getElementById('accompanying-teacher-select-text');
            const dropdown = document.getElementById('accompanying-teacher-dropdown');

            if (teacherInput) {
                teacherInput.value = value;
            }

            if (teacherText) {
                teacherText.textContent = label;
            }

            if (dropdown) {
                dropdown.classList.add('hidden');
            }

            document.querySelectorAll('.accompanying-teacher-option').forEach(option => {
                if (option.getAttribute('data-value') === value) {
                    option.classList.add('bg-blue-50');
                } else {
                    option.classList.remove('bg-blue-50');
                }
            });
        }

        function toggleTeacherParticipantDropdown() {
            const dropdown = document.getElementById('teacher-participant-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        function selectTeacherParticipant(value, label) {
            const teacherInput = document.getElementById('teacher_participant_2');
            const teacherText = document.getElementById('teacher-participant-select-text');
            const dropdown = document.getElementById('teacher-participant-dropdown');

            if (teacherInput) {
                teacherInput.value = value;
            }

            if (teacherText) {
                teacherText.textContent = label;
            }

            if (dropdown) {
                dropdown.classList.add('hidden');
            }

            document.querySelectorAll('.teacher-participant-option').forEach(option => {
                if (option.getAttribute('data-value') === value) {
                    option.classList.add('bg-green-50');
                } else {
                    option.classList.remove('bg-green-50');
                }
            });
        }


        document.addEventListener('click', function(event) {
            // Проверяем, не кликнули ли мы на кнопку приказа или внутри формы приказа
            let target = event.target;
            let clickedOnOrderButton = false;
            let clickedInOrderForm = false;
            
            // Проверяем, кликнули ли на кнопку приказа
            while (target && target !== document.body) {
                if (target.tagName === 'BUTTON' && target.getAttribute('onclick') && target.getAttribute('onclick').includes('showOrderForm')) {
                    clickedOnOrderButton = true;
                    break;
                }
                if (target.id && (target.id === 'order-1-form' || target.id === 'order-2-form' || target.id === 'order-3-form' || target.id === 'order-4-form')) {
                    clickedInOrderForm = true;
                    break;
                }
                target = target.parentElement;
            }
            
            // Если клик был на кнопке приказа или внутри формы приказа, не обрабатываем
            if (clickedOnOrderButton || clickedInOrderForm) {
                return;
            }
            
            const studentDropdown = document.getElementById('student-dropdown');
            const studentButton = document.getElementById('student-select-button');
            const placeDropdown = document.getElementById('place-dropdown');
            const placeButton = document.getElementById('place-select-button');
            const accompanyingTeacherDropdown = document.getElementById('accompanying-teacher-dropdown');
            const accompanyingTeacherButton = document.getElementById('accompanying-teacher-select-button');
            const teacherParticipantDropdown = document.getElementById('teacher-participant-dropdown');
            const teacherParticipantButton = document.getElementById('teacher-participant-select-button');
            
            if (studentDropdown && studentButton) {
                if (!studentDropdown.contains(event.target) && !studentButton.contains(event.target)) {
                    studentDropdown.classList.add('hidden');
                }
            }

            if (placeDropdown && placeButton) {
                if (!placeDropdown.contains(event.target) && !placeButton.contains(event.target)) {
                    placeDropdown.classList.add('hidden');
                }
            }

            if (accompanyingTeacherDropdown && accompanyingTeacherButton) {
                if (!accompanyingTeacherDropdown.contains(event.target) && !accompanyingTeacherButton.contains(event.target)) {
                    accompanyingTeacherDropdown.classList.add('hidden');
                }
            }

            if (teacherParticipantDropdown && teacherParticipantButton) {
                if (!teacherParticipantDropdown.contains(event.target) && !teacherParticipantButton.contains(event.target)) {
                    teacherParticipantDropdown.classList.add('hidden');
                }
            }

        });

        function showOrderForm(orderNumber) {
            // Скрываем все формы
            const form1 = document.getElementById('order-1-form');
            const form2 = document.getElementById('order-2-form');
            const form3 = document.getElementById('order-3-form');
            const form4 = document.getElementById('order-4-form');
            
            if (form1) form1.classList.add('hidden');
            if (form2) form2.classList.add('hidden');
            if (form3) form3.classList.add('hidden');
            if (form4) form4.classList.add('hidden');
            
            // Если передан 0, просто скрываем все формы
            if (orderNumber === 0) {
                return;
            }

            if ((orderNumber === 1 || orderNumber === 2) && typeof window.syncResponsibleTeacherOrderFields === 'function') {
                window.syncResponsibleTeacherOrderFields();
            }
            
            // Показываем нужную форму
            const formId = 'order-' + orderNumber + '-form';
            const form = document.getElementById(formId);
            if (form) {
                form.classList.remove('hidden');
            }
        }

        // Persist order form fields per competition (localStorage)
        (function initOrderFormPersistence() {
            const competitionId = {{ (int) $competition->id }};
            const prefix = `competition:${competitionId}:orderField:`;
            const fieldIds = [
                'order1_date',
                'dispatcher',
                'deputy_director_1',
                'director_name_1',
                'order2_date',
                'head_of_studies_2',
                'deputy_director_2',
                'director_name_2',
                'order3_date',
                'location_classes',
                'application_date',
            ];

            function keyFor(id) {
                return prefix + id;
            }

            // restore
            fieldIds.forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                const saved = localStorage.getItem(keyFor(id));
                if (saved === null) return;
                // Don't override server-provided value if it differs and non-empty
                const current = (el.value ?? '').toString();
                if (current.trim() !== '' && current !== saved) return;
                el.value = saved;
            });

            // save on change/input
            fieldIds.forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                const handler = function () {
                    try {
                        localStorage.setItem(keyFor(id), (el.value ?? '').toString());
                    } catch (e) {}
                };
                el.addEventListener('input', handler);
                el.addEventListener('change', handler);
            });
        })();

        // Функции для редактирования результата
        function editResult(id, place) {
            document.getElementById('edit-result-id').value = id;
            document.getElementById('edit-result-place').value = place;
            document.getElementById('edit-result-modal').classList.remove('hidden');
            document.getElementById('edit-result-error').classList.add('hidden');
        }

        function closeEditResultModal() {
            document.getElementById('edit-result-modal').classList.add('hidden');
            document.getElementById('edit-result-form').reset();
        }

        // Обработка формы редактирования результата
        document.getElementById('edit-result-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const resultId = document.getElementById('edit-result-id').value;
            const errorDiv = document.getElementById('edit-result-error');
            const place = document.getElementById('edit-result-place').value.trim();

            if (!place) {
                errorDiv.textContent = 'Поле места обязательно для заполнения';
                errorDiv.classList.remove('hidden');
                return;
            }

            errorDiv.classList.add('hidden');

            const formData = new FormData(form);
            formData.append('_method', 'PUT');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const response = await fetch(`/competitions/{{ $competition->id }}/results/${resultId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeEditResultModal();
                    window.location.reload();
                } else {
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка при обновлении результата');
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                errorDiv.textContent = 'Произошла ошибка при обновлении результата';
                errorDiv.classList.remove('hidden');
            }
        });

        // Закрытие модального окна при клике вне его
        document.getElementById('edit-result-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditResultModal();
            }
        });

        // Функции для работы с dropdown места
        function togglePlaceDropdown() {
            const dropdown = document.getElementById('place-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectPlace(place) {
            const hiddenInput = document.getElementById('place');
            const buttonText = document.getElementById('place-select-text');
            const dropdown = document.getElementById('place-dropdown');
            
            hiddenInput.value = place;
            buttonText.textContent = place;
            
            document.querySelectorAll('.place-option').forEach(option => {
                option.classList.remove('bg-blue-100');
                if (option.getAttribute('data-place') == place) {
                    option.classList.add('bg-blue-100');
                }
            });
            
            dropdown.classList.add('hidden');
        }

        document.querySelectorAll('.personal-result-place-input').forEach(function (input) {
            input.addEventListener('input', function () {
                const value = String(input.value || '').trim();
                if (value !== '' && Number(value) <= 0) {
                    input.value = '';
                }
                input.setCustomValidity('');
            });
        });

        document.getElementById('personal-results-form')?.addEventListener('submit', function (e) {
            let blocked = false;

            this.querySelectorAll('.personal-result-place-input').forEach(function (input) {
                const value = String(input.value || '').trim();
                if (value !== '' && Number(value) <= 0) {
                    input.setCustomValidity('Место должно быть больше 0.');
                    if (!blocked) {
                        input.reportValidity();
                        blocked = true;
                    }
                } else {
                    input.setCustomValidity('');
                }
            });

            if (blocked || !this.checkValidity()) {
                e.preventDefault();
                if (!blocked) {
                    this.reportValidity();
                }
            }
        });

        // Обработка формы добавления результата
        document.getElementById('add-result-form-submit')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;

            submitButton.disabled = true;
            submitButton.textContent = 'Добавление...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success && data.result) {
                    document.getElementById('add-result-form')?.classList.add('hidden');
                    document.getElementById('add-result-toggle')?.classList.add('hidden');

                    const resultsSection = document.getElementById('competition-results');
                    const resultsList = document.getElementById('competition-results-list');
                    if (resultsSection) {
                        resultsSection.setAttribute('data-has-result', '1');
                    }

                    let resultsTable = document.getElementById('competition-results-tbody');

                    if (!resultsTable && resultsList) {
                        document.getElementById('competition-results-empty')?.remove();
                        const resultsType = document.getElementById('competition-results')?.dataset.resultsType || 'team';
                        const middleHeaders = resultsType === 'personal'
                            ? `<th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участник</th>
                               <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вид спорта</th>`
                            : `<th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>`;
                        resultsList.innerHTML = `
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                            ${middleHeaders}
                                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody id="competition-results-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                                </table>
                            </div>
                        `;
                        resultsTable = document.getElementById('competition-results-tbody');
                    }
                    
                    // Добавляем результат в таблицу
                    if (resultsTable) {
                        const newRow = document.createElement('tr');
                        const escapedPlace = String(data.result.place).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                        newRow.setAttribute('data-result-id', String(data.result.id));
                        newRow.innerHTML = `
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">${data.result.place}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">${data.result.category_name}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button"
                                        onclick="editResult(${data.result.id}, '${escapedPlace}')"
                                        class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded hover:bg-blue-50 transition"
                                    >
                                        Редактировать
                                    </button>
                                    <form 
                                        action="/competitions/{{ $competition->id }}/results/${data.result.id}" 
                                        method="POST" 
                                        class="inline"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить этот результат?')"
                                    >
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button 
                                            type="submit" 
                                            class="text-red-600 hover:text-red-900 px-3 py-1 rounded hover:bg-red-50 transition"
                                        >
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </td>
                        `;
                        resultsTable.appendChild(newRow);
                    }

                    const feedback = document.getElementById('competition-results-feedback');
                    if (feedback) {
                        feedback.className = 'mb-4 rounded border-l-4 border-green-400 bg-green-50 p-4';
                        feedback.innerHTML = '<p class="text-sm text-green-700">' +
                            (data.message || 'Результат успешно добавлен!') +
                            ' Место: <strong>' + data.result.place + '</strong>.</p>';
                        feedback.classList.remove('hidden');
                    }

                    const placeInput = document.getElementById('place');
                    const placeText = document.getElementById('place-select-text');
                    if (placeInput) placeInput.value = '';
                    if (placeText) placeText.textContent = 'Выберите место';
                    form.reset();
                } else {
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка при добавлении результата');
                    const errorFeedback = document.getElementById('competition-results-feedback');
                    if (errorFeedback) {
                        errorFeedback.className = 'mb-4 rounded border-l-4 border-red-400 bg-red-50 p-4';
                        errorFeedback.innerHTML = '<p class="text-sm text-red-700">' + errorMessage + '</p>';
                        errorFeedback.classList.remove('hidden');
                    } else {
                        alert(errorMessage);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                const errorFeedback = document.getElementById('competition-results-feedback');
                if (errorFeedback) {
                    errorFeedback.className = 'mb-4 rounded border-l-4 border-red-400 bg-red-50 p-4';
                    errorFeedback.innerHTML = '<p class="text-sm text-red-700">Произошла ошибка при добавлении результата.</p>';
                    errorFeedback.classList.remove('hidden');
                } else {
                    alert('Произошла ошибка при добавлении результата');
                }
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });

        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('add_result') !== '1') {
                return;
            }
            const section = document.getElementById('competition-results');
            if (section?.getAttribute('data-has-result') === '1') {
                return;
            }
            const form = document.getElementById('add-result-form');
            if (!form) {
                return;
            }
            form.classList.remove('hidden');
            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })();
        
    </script>
@endsection



