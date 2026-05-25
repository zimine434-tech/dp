@extends('layouts.teacher')

@section('title', 'Команды')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Команды</h1>
            </div>
            <a 
                href="{{ route('teams.create') }}" 
                class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="hidden sm:inline">Создать команду</span>
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

        <!-- Список команд -->
        @if($teams->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
                @foreach($teams as $team)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden flex flex-col h-full">
                        <div class="p-5 sm:p-6 flex flex-col flex-1 min-h-0">
                            <!-- Заголовок и бейдж -->
                            <div class="mb-4">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight flex-1 min-w-0 break-words">{{ $team->name }}</h3>
                                    <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        @php
                                            $count = $team->members->whereNull('out')->count();
                                            $lastDigit = $count % 10;
                                            $lastTwoDigits = $count % 100;
                                            
                                            if ($count === 0 || ($lastTwoDigits >= 5 && $lastTwoDigits <= 20) || $lastDigit >= 5 || $lastDigit === 0) {
                                                $text = $count . ' участников';
                                            } elseif ($lastDigit === 1) {
                                                $text = $count . ' участник';
                                            } else {
                                                $text = $count . ' участника';
                                            }
                                        @endphp
                                        {{ $text }}
                                    </span>
                                </div>
                                <!-- Описание -->
                                <div class="mb-4 flex-1 min-h-0">
                                    @if(filled(trim(strip_tags((string) ($team->description ?? '')))))
                                        <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($team->description))), 240) }}</p>
                                    @else
                                        <p class="text-gray-400 text-sm italic">Описание отсутствует</p>
                                    @endif
                                </div>
                                @if($team->sport)
                                    <p class="text-sm text-gray-600 mb-4">
                                        <span class="text-gray-500">Вид спорта:</span>
                                        <span class="font-medium text-gray-800">{{ $team->sport->name }}</span>
                                    </p>
                                @else
                                    <p class="text-sm italic text-gray-500 mb-4">Вид спорта не указан</p>
                                @endif
                            </div>

                            <!-- Дата создания -->
                            <div class="flex items-center text-xs text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Создана {{ $team->created_at->format('d.m.Y') }}</span>
                            </div>

                            <!-- Кнопки действий -->
                            <div class="flex gap-2 mt-auto">
                                <a 
                                    href="{{ route('teams.show', ['team' => $team]) }}" 
                                    class="flex-1 text-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors text-sm font-medium shadow-sm"
                                >
                                    Подробнее
                                </a>
                                <a 
                                    href="{{ route('teams.edit', $team) }}" 
                                    class="flex-1 text-center px-4 py-2.5 bg-white text-gray-800 rounded-lg hover:bg-gray-50 active:bg-gray-100 transition-colors text-sm font-medium border-2 border-gray-300 shadow-sm hover:border-gray-400"
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
                                        class="px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 active:bg-red-800 transition-colors text-sm font-medium shadow-sm"
                                        title="Удалить команду"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $teams->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет команд</h3>
                <p class="mt-1 text-sm text-gray-500">Начните с создания новой команды.</p>
                <div class="mt-6">
                    <a 
                        href="{{ route('teams.create') }}" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Создать команду
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script>
        function confirmDeleteTeam(teamName) {
            const message = 'ВНИМАНИЕ!\n\nВы уверены, что хотите удалить команду "' + teamName + '"?\n\n' +
                'Команда удалится; связи в тренировках и соревнованиях с этой командой перестанут отображаться корректно, пока их не обновят.\n\n' +
                'Привязку вида спорта к новой команде задают в карточке команды.\n\n' +
                'Это действие нельзя отменить!';
            return confirm(message);
        }
    </script>
@endsection


