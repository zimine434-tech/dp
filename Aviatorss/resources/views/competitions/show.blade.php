@extends('layouts.teacher')

@section('title', 'Детали соревнования')

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
                
                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Вид спорта</label>
                    <p class="text-lg text-gray-900">
                        <a href="{{ route('sports.show', $competition->sport) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $competition->sport->name }}
                        </a>
                    </p>
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
                <h2 class="text-xl font-semibold text-gray-800">{{ $text }} ({{ $count }})</h2>
                
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
                    <form action="{{ route('competitions.participants.add', $competition) }}" method="POST" class="space-y-4 overflow-visible">
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

            @if($competition->participants->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Группа</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                                <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($competition->participants as $participant)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $participant->user->lastname }}
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $participant->user->firstname }}
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            @if($participant->user->role === 'teacher')
                                                Преподаватель
                                            @else
                                                {{ $participant->user->group_name ?? 'Группа не указана' }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        @php
                                            $currentRole = $participant->user->role ?? 'student';
                                            $roleLabels = [
                                                'student' => 'Участник',
                                                'teacher' => 'Преподаватель'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $roleLabels[$currentRole] ?? 'Участник' }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-right text-sm font-medium">
                                        <form 
                                            action="{{ route('competitions.participants.remove', [$competition, $participant->user]) }}" 
                                            method="POST" 
                                            class="inline"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить этого участника из соревнования?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            @include('competitions.partials.show-context-fields')
                                            <button 
                                                type="submit" 
                                                class="text-red-600 hover:text-red-900 px-3 py-1 rounded hover:bg-red-50 transition"
                                            >
                                                Удалить
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">Пока нет участников в этом соревновании.</p>
            @endif
        </div>

        

        <!-- Приказы -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Приказы</h2>
            
            @if(auth()->user()->role === 'teacher')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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
                                $teachers = $competition->participants->where('role', 'teacher');
                                $selectedTeacherId = old('accompanying_teacher', '');
                                $selectedTeacher = $teachers->firstWhere('user_id', $selectedTeacherId);
                            @endphp
                            <input type="hidden" name="accompanying_teacher" id="accompanying_teacher" value="{{ $selectedTeacherId }}" required>
                            <button 
                                type="button"
                                id="accompanying-teacher-select-button"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('accompanying_teacher') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                                onclick="toggleAccompanyingTeacherDropdown()"
                            >
                                <span id="accompanying-teacher-select-text" class="text-gray-700">
                                    @if($selectedTeacher)
                                        {{ $selectedTeacher->user->lastname }} {{ $selectedTeacher->user->firstname }} {{ $selectedTeacher->user->patronymic ?? '' }}
                                    @else
                                        Выберите преподавателя
                                    @endif
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="accompanying-teacher-dropdown" class="hidden absolute z-40 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                                <div class="overflow-y-auto flex-1 min-h-0">
                                    @foreach($teachers as $teacher)
                                        <div 
                                            class="accompanying-teacher-option px-4 py-3 hover:bg-blue-50 cursor-pointer {{ $selectedTeacherId == $teacher->user->id ? 'bg-blue-50' : '' }}"
                                            data-value="{{ $teacher->user->id }}"
                                            onclick="selectAccompanyingTeacher('{{ $teacher->user->id }}', '{{ addslashes($teacher->user->lastname . ' ' . $teacher->user->firstname . ' ' . ($teacher->user->patronymic ?? '')) }}')"
                                        >
                                            <div class="font-medium text-gray-900">{{ $teacher->user->lastname }} {{ $teacher->user->firstname }} {{ $teacher->user->patronymic ?? '' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if($teachers->count() == 0)
                                <p class="mt-1 text-sm text-yellow-600">В соревновании нет участников с ролью "Преподаватель". Добавьте преподавателя в участники соревнования.</p>
                            @endif
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
                                $teachers2 = $competition->participants->where('role', 'teacher');
                                $selectedTeacherId2 = old('teacher_participant', '');
                                $selectedTeacher2 = $teachers2->firstWhere('user_id', $selectedTeacherId2);
                            @endphp
                            <input type="hidden" name="teacher_participant" id="teacher_participant_2" value="{{ $selectedTeacherId2 }}" required>
                            <button 
                                type="button"
                                id="teacher-participant-select-button"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('teacher_participant') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                                onclick="toggleTeacherParticipantDropdown()"
                            >
                                <span id="teacher-participant-select-text" class="text-gray-700">
                                    @if($selectedTeacher2)
                                        {{ $selectedTeacher2->user->lastname }} {{ $selectedTeacher2->user->firstname }} {{ $selectedTeacher2->user->patronymic ?? '' }}
                                    @else
                                        Выберите преподавателя
                                    @endif
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="teacher-participant-dropdown" class="hidden absolute z-40 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                                <div class="overflow-y-auto flex-1 min-h-0">
                                    @foreach($teachers2 as $teacher)
                                        <div 
                                            class="teacher-participant-option px-4 py-3 hover:bg-green-50 cursor-pointer {{ $selectedTeacherId2 == $teacher->user->id ? 'bg-green-50' : '' }}"
                                            data-value="{{ $teacher->user->id }}"
                                            onclick="selectTeacherParticipant('{{ $teacher->user->id }}', '{{ addslashes($teacher->user->lastname . ' ' . $teacher->user->firstname . ' ' . ($teacher->user->patronymic ?? '')) }}')"
                                        >
                                            <div class="font-medium text-gray-900">{{ $teacher->user->lastname }} {{ $teacher->user->firstname }} {{ $teacher->user->patronymic ?? '' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if($teachers2->count() == 0)
                                <p class="mt-1 text-sm text-yellow-600">В соревновании нет участников с ролью "Преподаватель". Добавьте преподавателя в участники соревнования.</p>
                            @endif
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
            @endif
        </div>

        <!-- Результаты соревнования -->
        @php
            $hasCompetitionResult = $competition->results->count() > 0;
        @endphp
        <div
            id="competition-results"
            class="bg-white rounded-lg shadow-md p-6"
            data-competition-id="{{ $competition->id }}"
            data-has-result="{{ $hasCompetitionResult ? '1' : '0' }}"
        >
            <div id="competition-results-feedback" class="hidden mb-4" role="status"></div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Результаты соревнования</h2>
                @if(($competition->status === 'finished' || $competition->status === 'ongoing') && ! $hasCompetitionResult)
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
                <p class="text-gray-500 text-sm">Результаты можно добавлять только для завершенных или текущих соревнований.</p>
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
                @if($competition->results->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                                <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody id="competition-results-tbody" class="bg-white divide-y divide-gray-200">
                                @foreach($competition->results->sortBy(function($r) {
                                    // Сортируем: сначала числа, потом текст
                                    if (is_numeric($r->place)) {
                                        return (int)$r->place;
                                    }
                                    return 9999 + ord($r->place[0] ?? 'z');
                                }) as $result)
                                    <tr>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ $result->place }}</span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $competition->category->name_category ?? 'Не указана' }}</span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <button 
                                                    type="button"
                                                    onclick="editResult({{ $result->id }}, '{{ addslashes($result->place) }}')"
                                                    class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded hover:bg-blue-50 transition"
                                                >
                                                    Редактировать
                                                </button>
                                                <form 
                                                    action="{{ route('competitions.results.destroy', [$competition, $result]) }}" 
                                                    method="POST" 
                                                    class="inline"
                                                    onsubmit="return confirm('Вы уверены, что хотите удалить этот результат?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit" 
                                                        class="text-red-600 hover:text-red-900 px-3 py-1 rounded hover:bg-red-50 transition"
                                                    >
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
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
                
                const url = '{{ route("competitions.search-students", $competition) }}?search=' + encodeURIComponent(search);
                
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
                if (target.id && (target.id === 'order-1-form' || target.id === 'order-2-form' || target.id === 'order-3-form')) {
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
            
            if (form1) form1.classList.add('hidden');
            if (form2) form2.classList.add('hidden');
            if (form3) form3.classList.add('hidden');
            
            // Если передан 0, просто скрываем все формы
            if (orderNumber === 0) {
                return;
            }
            
            // Показываем нужную форму
            const formId = 'order-' + orderNumber + '-form';
            const form = document.getElementById(formId);
            if (form) {
                form.classList.remove('hidden');
            }
        }

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
                        resultsList.innerHTML = `
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
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



