@extends('layouts.teacher')

@section('title', $team->name)

@push('styles')
<style>
    #team-tab-panels {
        position: relative;
    }
    #team-tab-panels > .tab-content.hidden {
        display: none !important;
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    @media (min-width: 1024px) {
        #team-overview-left-card {
            height: 100%;
        }
    }
</style>
@endpush

@section('content')
    <div class="space-y-4 sm:space-y-6">
        <!-- Заголовок -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $team->name }}</h1>
                @if(filled($team->description))
                    @include('partials.rich-text', ['html' => $team->description, 'class' => 'mt-2 text-sm sm:text-base text-gray-600 leading-relaxed'])
                @endif
                @if($team->sport)
                    <p class="text-gray-600 mt-2 text-sm sm:text-base">
                        <span class="font-medium text-gray-800">{{ $team->sport->name }}</span>
                    </p>
                @endif
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:space-x-3">
                <a 
                    href="{{ route('teams.edit', $team) }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border-2 border-gray-300 bg-white text-gray-800 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition text-center text-sm sm:text-base font-medium"
                >
                    Редактировать
                </a>
                <form 
                    action="{{ route('teams.destroy', $team) }}" 
                    method="POST" 
                    class="inline"
                    onsubmit="return confirmDeleteTeam('{{ $team->name }}')"
                >
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center text-sm sm:text-base"
                    >
                        Удалить команду
                    </button>
                </form>
                <a 
                    href="{{ route('teams.index') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-center text-sm sm:text-base"
                >
                    Назад к списку
                </a>
            </div>
        </div>

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

        @php
            $pendingJoinRequestsCount = \App\Models\TeamJoinRequest::query()
                ->where('team_id', $team->id)
                ->where('status', 'pending')
                ->count();
        @endphp

        @php
            $currentMembers = $team->members->whereNull('out');
            $currentMemberUserIds = $currentMembers->pluck('user_id')->filter()->all();
            // В истории может быть несколько записей на одного пользователя (вступал/выбывал несколько раз).
            // Показываем только последнюю (по out).
            $formerMembers = $team->members
                ->whereNotNull('out')
                ->whereNotIn('user_id', $currentMemberUserIds)
                ->sortByDesc(fn ($m) => optional($m->out)->timestamp ?? 0)
                ->unique('user_id')
                ->values();
        @endphp

        <div id="team-overview-columns" class="flex flex-col items-stretch gap-6 lg:flex-row">
            <!-- Основная информация с табами -->
            <div id="team-overview-left-wrap" class="flex min-w-0 w-full flex-col lg:flex-[2]">
                <div id="team-overview-left-card" class="flex min-h-0 flex-1 flex-col rounded-lg bg-white shadow-md lg:min-h-full">
                    <!-- Табы -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px" aria-label="Tabs">
                            <button 
                                onclick="switchTab('description')" 
                                id="tab-description"
                                class="tab-button px-5 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300"
                            >
                                Описание команды
                            </button>
                            <button 
                                onclick="switchTab('add-member')" 
                                id="tab-add-member"
                                class="tab-button active px-5 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600"
                            >
                                Добавить участника
                            </button>
                            <button 
                                onclick="switchTab('join-requests')" 
                                id="tab-join-requests"
                                class="tab-button px-5 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300"
                            >
                                Заявки
                                @if(($pendingJoinRequestsCount ?? 0) > 0)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                        {{ $pendingJoinRequestsCount }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                onclick="switchTab('remove-member')" 
                                id="tab-remove-member"
                                class="tab-button px-5 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300"
                            >
                                Удалить участника
                            </button>
                        </nav>
                    </div>

                    <!-- Контент табов -->
                    <div id="team-tab-panels" class="flex min-h-0 flex-1 flex-col p-4">
                        <!-- Таб: Описание -->
                        <div id="content-description" class="tab-content hidden w-full">
                            <h2 class="text-lg font-semibold text-gray-800 mb-3">Описание команды</h2>
                            @if(filled($team->description))
                                @include('partials.rich-text', ['html' => $team->description, 'class' => 'text-gray-700'])
                            @else
                                <p class="text-gray-400 italic">Описание отсутствует</p>
                            @endif

                            <div class="mt-6 pt-6 border-t">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 block mb-1">Дата создания:</span>
                                        <p class="text-gray-900 font-medium">{{ $team->created_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block mb-1">Последнее обновление:</span>
                                        <p class="text-gray-900 font-medium">{{ $team->updated_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Таб: Добавить участника -->
                        <div id="content-add-member" class="tab-content flex min-h-0 w-full flex-1 flex-col">
                            <h2 class="mb-2 shrink-0 text-base font-semibold text-gray-800">Добавить участника в команду</h2>
                            
                            <form action="{{ route('teams.members.add', $team) }}" method="POST" class="flex min-h-0 flex-1 flex-col overflow-visible">
                                <div class="space-y-2.5">
                                @csrf
                                
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
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
                                            class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-left focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
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

                                <div class="relative overflow-visible">
                                    <label for="type_user" class="mb-1 block text-sm font-medium text-gray-700">
                                        Роль в команде
                                    </label>
                                    <!-- Скрытое поле для формы -->
                                    <input type="hidden" name="type_user" id="type_user" value="{{ old('type_user', 'member') }}">
                                    
                                    <!-- Кнопка выбора роли -->
                                    <button 
                                        type="button"
                                        id="team-role-select-button"
                                        class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-left focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('type_user') border-red-500 @enderror"
                                        onclick="toggleTeamRoleDropdown()"
                                    >
                                        <span id="team-role-select-text" class="text-gray-700">
                                            @php
                                                $roleOptions = [
                                                    'member' => 'Участник',
                                                    'capitan' => 'Капитан'
                                                ];
                                                $selectedRole = old('type_user', 'member');
                                            @endphp
                                            {{ $roleOptions[$selectedRole] ?? 'Участник' }}
                                        </span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown меню -->
                                    <div id="team-role-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                                        <!-- Список ролей -->
                                        <div id="team-role-list" class="overflow-y-auto flex-1 min-h-0">
                                            @php
                                                $roleOptions = [
                                                    'member' => ['label' => 'Участник', 'description' => 'Обычный участник команды'],
                                                    'capitan' => ['label' => 'Капитан', 'description' => 'Капитан команды']
                                                ];
                                                $selectedRole = old('type_user', 'member');
                                            @endphp
                                            @foreach($roleOptions as $value => $option)
                                                <div 
                                                    class="team-role-option px-4 py-3 hover:bg-blue-50 cursor-pointer {{ $selectedRole === $value ? 'bg-blue-100' : '' }}"
                                                    data-value="{{ $value }}"
                                                    onclick="selectTeamRole('{{ $value }}', '{{ addslashes($option['label']) }}')"
                                                >
                                                    <div class="font-medium text-gray-900">{{ $option['label'] }}</div>
                                                    <div class="text-sm text-gray-500">{{ $option['description'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('type_user')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                </div>

                                <div class="mt-auto flex shrink-0 justify-end pt-2">
                                    <button 
                                        type="submit" 
                                        class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-medium text-white transition hover:bg-blue-700"
                                    >
                                        Добавить участника
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Таб: Заявки на вступление -->
                        <div id="content-join-requests" class="tab-content hidden w-full">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Заявки на вступление</h2>
                                <a href="{{ route('teams.join-requests.index', $team) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    Открыть отдельной страницей →
                                </a>
                            </div>

                            @php
                                $pendingJoinRequests = \App\Models\TeamJoinRequest::query()
                                    ->with('user')
                                    ->where('team_id', $team->id)
                                    ->where('status', 'pending')
                                    ->latest('created_at')
                                    ->take(20)
                                    ->get();
                            @endphp

                            @if($pendingJoinRequests->isEmpty())
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
                                    Заявок на вступление пока нет.
                                </div>
                            @else
                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Студент</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Сообщение</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($pendingJoinRequests as $r)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $r->user?->lastname }} {{ $r->user?->firstname }} {{ $r->user?->patronymic }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">{{ $r->created_at?->format('d.m.Y H:i') }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-700">
                                                        @if(filled($r->message))
                                                            <div class="max-w-xl whitespace-pre-wrap">{{ $r->message }}</div>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                                            <form method="POST" action="{{ route('teams.join-requests.approve', [$team, $r]) }}">
                                                                @csrf
                                                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                                                                    Принять
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('teams.join-requests.reject', [$team, $r]) }}">
                                                                @csrf
                                                                <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                                                    Отклонить
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if(($pendingJoinRequestsCount ?? 0) > $pendingJoinRequests->count())
                                    <p class="mt-3 text-sm text-gray-500">
                                        Показаны последние {{ $pendingJoinRequests->count() }} заявок. Полный список — по ссылке выше.
                                    </p>
                                @endif
                            @endif
                        </div>

                        <!-- Таб: Удалить участника -->
                        <div id="content-remove-member" class="tab-content hidden w-full">
                            <h2 class="text-lg font-semibold text-gray-800 mb-3">Удалить участника из команды</h2>
                            
                            @if($currentMembers->count() > 0)
                                <!-- Поиск -->
                                <div class="mb-3">
                                    <label for="search-member" class="mb-1 block text-sm font-medium text-gray-700">
                                        Поиск по фамилии, имени и отчеству
                                    </label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            id="search-member" 
                                            name="search-member"
                                            placeholder="Введите фамилию, имя или отчество..."
                                            class="w-full rounded-lg border border-gray-300 py-1.5 pl-10 pr-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                            oninput="filterMembers(this.value)"
                                        >
                                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div id="members-list" class="space-y-3 hidden">
                                    @foreach($currentMembers as $member)
                                        <div 
                                            class="member-item flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition border border-gray-200"
                                            data-firstname="{{ strtolower($member->user->firstname) }}"
                                            data-lastname="{{ strtolower($member->user->lastname) }}"
                                            data-patronymic="{{ strtolower($member->user->patronymic ?? '') }}"
                                            style="display: none;"
                                        >
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $member->user->lastname }} {{ $member->user->firstname }} 
                                                    @if($member->user->patronymic)
                                                        {{ $member->user->patronymic }}
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $member->user->login }}</div>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @if($member->type_user === 'coach')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                            Тренер
                                                        </span>
                                                    @elseif($member->type_user === 'capitan')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            Капитан
                                                        </span>
                                                    @elseif($member->type_user === 'member')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            Участник
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <form 
                                                action="{{ route('teams.members.remove', ['team' => $team, 'member' => $member]) }}" 
                                                method="POST" 
                                                class="ml-4"
                                                onsubmit="return confirm('Вы уверены, что хотите удалить {{ $member->user->firstname }} {{ $member->user->lastname }} из команды?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition flex items-center shadow-sm"
                                                >
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Удалить
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div id="no-results" class="hidden text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Ничего не найдено</h3>
                                    <p class="mt-1 text-sm text-gray-500">Попробуйте изменить поисковый запрос.</p>
                                </div>
                                
                                <div id="empty-search" class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Введите данные для поиска</h3>
                                    <p class="mt-1 text-sm text-gray-500">Начните вводить фамилию, имя или отчество для поиска участников.</p>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет участников в команде</h3>
                                    <p class="mt-1 text-sm text-gray-500">В команде пока нет участников для удаления.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика и ближайшая тренировка -->
            <div id="team-overview-right" class="flex min-w-0 w-full flex-col gap-5 lg:flex-1">
                <div class="rounded-lg bg-white p-5 shadow-md">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Статистика</h2>
                    <div class="space-y-4">
                        <div class="rounded-lg bg-blue-50 p-3">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <div>
                                    @php
                                        $count = $team->members->whereNull('out')->count();
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
                                    <p class="text-sm text-gray-600">{{ $text }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('teams.partials.nearest-training', [
                    'nearestTraining' => $nearestTraining ?? null,
                    'inSidebar' => true,
                ])
            </div>
        </div>

        <!-- Участники команды -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 pb-0">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Участники команды</h2>
            </div>

            <div class="border-b border-gray-200 px-6">
                <nav class="flex -mb-px" aria-label="Состав команды">
                    <button
                        type="button"
                        id="tab-roster-current"
                        class="roster-tab-button active px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600"
                        onclick="switchRosterTab('current')"
                    >
                        Текущий состав ({{ $currentMembers->count() }})
                    </button>
                    <button
                        type="button"
                        id="tab-roster-former"
                        class="roster-tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300"
                        onclick="switchRosterTab('former')"
                    >
                        Бывшие участники ({{ $formerMembers->count() }})
                    </button>
                </nav>
            </div>

            <div id="content-roster-current" class="roster-tab-content p-6">
                @if($currentMembers->count() > 0)
                        <div class="overflow-x-auto -mx-6 sm:mx-0" style="overflow-y: visible;">
                            <div class="inline-block min-w-full align-middle" style="overflow: visible;">
                                <div class="shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg" style="overflow: visible;">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Роль в команде</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Дата присоединения</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($currentMembers as $member)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $member->user->firstname }} {{ $member->user->lastname }}
                                                        </div>
                                                        <div class="mt-1 sm:hidden">
                                                            @if($member->type_user === 'coach')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Тренер</span>
                                                            @elseif($member->type_user === 'capitan')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Капитан</span>
                                                            @elseif($member->type_user === 'member')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Участник</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                                                        <form
                                                            id="role-form-{{ $member->id }}"
                                                            action="{{ route('teams.members.update-role', ['team' => $team, 'member' => $member]) }}"
                                                            method="POST"
                                                            class="inline-flex items-center gap-2"
                                                        >
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="relative overflow-visible">
                                                                <input type="hidden" name="type_user" id="role-hidden-{{ $member->id }}" value="{{ $member->type_user }}">
                                                                <button
                                                                    type="button"
                                                                    id="role-select-button-{{ $member->id }}"
                                                                    class="text-xs px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between
                                                                        @if($member->type_user === 'capitan') bg-yellow-100 text-yellow-800
                                                                        @elseif($member->type_user === 'coach') bg-indigo-100 text-indigo-800
                                                                        @else bg-blue-100 text-blue-800
                                                                        @endif"
                                                                    onclick="toggleMemberRoleDropdown({{ $member->id }})"
                                                                >
                                                                    <span id="role-select-text-{{ $member->id }}" class="text-xs">
                                                                        @if($member->type_user === 'coach')
                                                                            Тренер
                                                                        @elseif($member->type_user === 'capitan')
                                                                            Капитан
                                                                        @else
                                                                            Участник
                                                                        @endif
                                                                    </span>
                                                                    <svg class="w-4 h-4 text-gray-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                    </svg>
                                                                </button>
                                                                <div id="role-dropdown-{{ $member->id }}" class="hidden fixed z-50 w-32 bg-white border border-gray-300 rounded-lg shadow-lg" style="display: none;">
                                                                    <div class="member-role-option px-3 py-2 hover:bg-indigo-50 cursor-pointer {{ $member->type_user === 'coach' ? 'bg-indigo-100' : '' }}" data-value="coach" onclick="selectMemberRole({{ $member->id }}, 'coach', 'Тренер')">
                                                                        <div class="text-xs font-medium text-gray-900">Тренер</div>
                                                                    </div>
                                                                    <div class="member-role-option px-3 py-2 hover:bg-blue-50 cursor-pointer {{ $member->type_user === 'member' ? 'bg-blue-100' : '' }}" data-value="member" onclick="selectMemberRole({{ $member->id }}, 'member', 'Участник')">
                                                                        <div class="text-xs font-medium text-gray-900">Участник</div>
                                                                    </div>
                                                                    <div class="member-role-option px-3 py-2 hover:bg-yellow-50 cursor-pointer {{ $member->type_user === 'capitan' ? 'bg-yellow-100' : '' }}" data-value="capitan" onclick="selectMemberRole({{ $member->id }}, 'capitan', 'Капитан')">
                                                                        <div class="text-xs font-medium text-gray-900">Капитан</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="submit" class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition">Сохранить</button>
                                                        </form>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                                        {{ $member->joined_at->format('d.m.Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                @else
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
                        Текущих участников нет.
                    </div>
                @endif
            </div>

            <div id="content-roster-former" class="roster-tab-content p-6 hidden">
                @if($formerMembers->count() > 0)
                        <div class="overflow-x-auto -mx-6 sm:mx-0">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Дата присоединения</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Дата выхода</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($formerMembers as $member)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $member->user->firstname }} {{ $member->user->lastname }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                                        {{ $member->joined_at?->format('d.m.Y') ?? '—' }}
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                                        {{ $member->out?->format('d.m.Y') ?? '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                @else
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
                        История выбывших пуста.
                    </div>
                @endif
            </div>
        </div>

        <!-- История добавлений и удалений -->
        @php
            $historyRows = collect();
            foreach ($team->members as $m) {
                $historyRows->push(['action' => 'add', 'date' => $m->joined_at, 'who' => $m->addedBy, 'user' => $m->user]);
                if ($m->out) {
                    $historyRows->push(['action' => 'remove', 'date' => $m->out, 'who' => $m->removedBy, 'user' => $m->user]);
                }
            }
            $historyRows = $historyRows->sortByDesc(fn($r) => $r['date']->timestamp)->values();
        @endphp
        <div class="bg-white rounded-lg shadow-md mt-6">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">История участников</h2>
                <p class="text-sm text-gray-500 mb-4">Кто и когда добавлял или удалял участников из команды.</p>
                @if($historyRows->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участник</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Кто выполнил</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($historyRows as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm">
                                            @if($row['action'] === 'add')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Добавление</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Удаление</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $row['user']->lastname }} {{ $row['user']->firstname }}
                                            @if($row['user']->patronymic) {{ $row['user']->patronymic }} @endif
                                            <span class="text-gray-500">({{ $row['user']->login }})</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $row['date']->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if($row['who'])
                                                {{ $row['who']->lastname }} {{ $row['who']->firstname }}
                                                @if($row['who']->patronymic) {{ $row['who']->patronymic }} @endif
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6">История пока пуста.</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Скрываем все табы
            document.querySelectorAll('#team-tab-panels > .tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Убираем активный класс со всех кнопок
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'text-blue-600', 'border-blue-600');
                button.classList.add('text-gray-500', 'border-transparent');
            });
            
            // Показываем выбранный таб
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Активируем выбранную кнопку
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.add('active', 'text-blue-600', 'border-blue-600');
            activeButton.classList.remove('text-gray-500', 'border-transparent');
        }

        function filterMembers(searchValue) {
            const searchTerm = searchValue.toLowerCase().trim().replace(/\s+/g, ' ');
            const memberItems = document.querySelectorAll('.member-item');
            const membersList = document.getElementById('members-list');
            const noResults = document.getElementById('no-results');
            const emptySearch = document.getElementById('empty-search');
            let visibleCount = 0;

            if (searchTerm === '') {
                // Если поиск пустой, скрываем все элементы
                memberItems.forEach(item => {
                    item.style.display = 'none';
                });
                membersList.classList.add('hidden');
                noResults.classList.add('hidden');
                emptySearch.classList.remove('hidden');
                return;
            }

            // Показываем список участников и скрываем сообщение о пустом поиске
            membersList.classList.remove('hidden');
            emptySearch.classList.add('hidden');

            // Разбиваем поисковый запрос на слова
            const searchWords = searchTerm.split(/\s+/).filter(word => word.length > 0);

            memberItems.forEach(item => {
                const firstname = (item.getAttribute('data-firstname') || '').trim();
                const lastname = (item.getAttribute('data-lastname') || '').trim();
                const patronymic = (item.getAttribute('data-patronymic') || '').trim();

                // Составляем полное имя в разных вариантах для поиска
                const fullName = `${lastname} ${firstname} ${patronymic}`.trim();
                const allFields = `${firstname} ${lastname} ${patronymic}`.trim();
                const allText = allFields.toLowerCase();

                // Если только одно слово - ищем его в любом поле
                if (searchWords.length === 1) {
                    const singleWord = searchWords[0];
                    const matches = 
                        firstname.includes(singleWord) || 
                        lastname.includes(singleWord) || 
                        patronymic.includes(singleWord) ||
                        allText.includes(singleWord);

                    if (matches) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                } else {
                    // Если несколько слов - проверяем, что все слова присутствуют
                    const allWordsMatch = searchWords.every(word => 
                        firstname.includes(word) || 
                        lastname.includes(word) || 
                        patronymic.includes(word) ||
                        allText.includes(word)
                    );

                    // Также проверяем точное совпадение фразы
                    const exactMatch = allText.includes(searchTerm) || fullName.toLowerCase().includes(searchTerm);

                    if (allWordsMatch || exactMatch) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                }
            });

            // Показываем или скрываем сообщение "Ничего не найдено"
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        function switchRosterTab(tabName) {
            document.querySelectorAll('.roster-tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.roster-tab-button').forEach(btn => {
                btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                btn.classList.add('text-gray-500', 'border-transparent');
            });

            const content = document.getElementById('content-roster-' + tabName);
            const button = document.getElementById('tab-roster-' + tabName);
            if (content) content.classList.remove('hidden');
            if (button) {
                button.classList.add('active', 'text-blue-600', 'border-blue-600');
                button.classList.remove('text-gray-500', 'border-transparent');
            }
        }

        function confirmDeleteTeam(teamName) {
            const message = 'ВНИМАНИЕ!\n\nВы уверены, что хотите удалить команду "' + teamName + '"?\n\n' +
                'Команда удалится, но при этом в спорте, тренировках и соревнованиях не будет отображена эта команда.\n\n' +
                'Команду можно добавить в редактировании вида спорта.\n\n' +
                'Это действие нельзя отменить!';
            return confirm(message);
        }

        // Поиск студентов для добавления в команду
        let studentSearchTimeout;
        let currentStudentSearchRequest = null;
        let activeStudentSearchTerm = '';

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
            
            clearTimeout(studentSearchTimeout);
            
            if (currentStudentSearchRequest) {
                currentStudentSearchRequest.abort();
                currentStudentSearchRequest = null;
            }
            
            if (search === '' || search.length < 2) {
                studentList.innerHTML = '';
                if (studentLoading) studentLoading.classList.add('hidden');
                if (studentNoResults) studentNoResults.classList.add('hidden');
                if (studentInitialMessage) studentInitialMessage.classList.remove('hidden');
                activeStudentSearchTerm = '';
                return;
            }
            
            activeStudentSearchTerm = search;
            
            studentList.innerHTML = '';
            if (studentInitialMessage) studentInitialMessage.classList.add('hidden');
            if (studentLoading) studentLoading.classList.remove('hidden');
            if (studentNoResults) studentNoResults.classList.add('hidden');
            
            studentSearchTimeout = setTimeout(() => {
                if (activeStudentSearchTerm !== search) {
                    return;
                }
                
                const xhr = new XMLHttpRequest();
                const requestSearchTerm = search;
                currentStudentSearchRequest = xhr;
                
                const url = '{{ route("teams.search-students", $team) }}?search=' + encodeURIComponent(search);
                
                xhr.open('GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                
                xhr.onload = function() {
                    if (activeStudentSearchTerm !== requestSearchTerm || currentStudentSearchRequest !== xhr) {
                        return;
                    }
                    
                    currentStudentSearchRequest = null;
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (activeStudentSearchTerm === requestSearchTerm) {
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
                    if (activeStudentSearchTerm === requestSearchTerm && currentStudentSearchRequest === xhr) {
                        studentList.innerHTML = '<div class="px-4 py-2 text-sm text-red-500 text-center">Ошибка соединения</div>';
                        if (studentLoading) studentLoading.classList.add('hidden');
                        currentStudentSearchRequest = null;
                    }
                };
                
                xhr.onabort = function() {
                    if (currentStudentSearchRequest === xhr) {
                        currentStudentSearchRequest = null;
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

        // Функции для работы с dropdown роли в команде
        function toggleTeamRoleDropdown() {
            const dropdown = document.getElementById('team-role-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectTeamRole(value, label) {
            const hiddenInput = document.getElementById('type_user');
            const buttonText = document.getElementById('team-role-select-text');
            const dropdown = document.getElementById('team-role-dropdown');
            
            hiddenInput.value = value;
            buttonText.textContent = label;
            
            document.querySelectorAll('.team-role-option').forEach(option => {
                option.classList.remove('bg-blue-100');
                if (option.getAttribute('data-value') === value) {
                    option.classList.add('bg-blue-100');
                }
            });
            
            dropdown.classList.add('hidden');
        }

        // Функции для работы с dropdown роли участника в таблице
        function toggleMemberRoleDropdown(memberId) {
            const dropdown = document.getElementById('role-dropdown-' + memberId);
            const button = document.getElementById('role-select-button-' + memberId);
            
            if (dropdown.classList.contains('hidden')) {
                // Показываем dropdown и позиционируем его
                const buttonRect = button.getBoundingClientRect();
                dropdown.style.left = buttonRect.left + 'px';
                dropdown.style.top = (buttonRect.bottom + 4) + 'px';
                dropdown.style.display = 'block';
                dropdown.classList.remove('hidden');
            } else {
                dropdown.style.display = 'none';
                dropdown.classList.add('hidden');
            }
        }

        function selectMemberRole(memberId, value, label) {
            const hiddenInput = document.getElementById('role-hidden-' + memberId);
            const buttonText = document.getElementById('role-select-text-' + memberId);
            const dropdown = document.getElementById('role-dropdown-' + memberId);
            const button = document.getElementById('role-select-button-' + memberId);
            
            hiddenInput.value = value;
            buttonText.textContent = label;
            
            // Обновляем цвет кнопки
            button.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-blue-100', 'text-blue-800', 'bg-gray-100', 'text-gray-800');
            if (value === 'capitan') {
                button.classList.add('bg-yellow-100', 'text-yellow-800');
            } else {
                button.classList.add('bg-blue-100', 'text-blue-800');
            }
            
            // Обновляем выделение в dropdown
            document.querySelectorAll('#role-dropdown-' + memberId + ' .member-role-option').forEach(option => {
                option.classList.remove('bg-blue-100', 'bg-yellow-100');
                if (option.getAttribute('data-value') === value) {
                    if (value === 'capitan') {
                        option.classList.add('bg-yellow-100');
                    } else {
                        option.classList.add('bg-blue-100');
                    }
                }
            });
            
            dropdown.style.display = 'none';
            dropdown.classList.add('hidden');
        }

        // Закрытие dropdown при клике вне его
        document.addEventListener('click', function(event) {
            const studentDropdown = document.getElementById('student-dropdown');
            const studentButton = document.getElementById('student-select-button');
            const roleDropdown = document.getElementById('team-role-dropdown');
            const roleButton = document.getElementById('team-role-select-button');
            
            if (studentDropdown && studentButton && !studentDropdown.contains(event.target) && !studentButton.contains(event.target)) {
                studentDropdown.classList.add('hidden');
            }
            
            if (roleDropdown && roleButton && !roleDropdown.contains(event.target) && !roleButton.contains(event.target)) {
                roleDropdown.classList.add('hidden');
            }
            
            // Закрываем все dropdown ролей участников
            document.querySelectorAll('[id^="role-dropdown-"]').forEach(dropdown => {
                const memberId = dropdown.id.replace('role-dropdown-', '');
                const button = document.getElementById('role-select-button-' + memberId);
                if (button && !dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.style.display = 'none';
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>
@endsection