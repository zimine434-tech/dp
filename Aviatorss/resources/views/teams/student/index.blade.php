@extends('layouts.student')

@section('title', 'Команды')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Команды</h1>
        </div>

        <!-- Список команд -->
        @if($teams->count() > 0)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                @foreach($teams as $team)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden flex flex-col h-full">
                        <div class="p-5 sm:p-6 flex flex-col flex-1 min-h-0">
                            <!-- Заголовок и бейдж -->
                            <div class="mb-4">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight flex-1 min-w-0 break-words">{{ $team->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap flex-shrink-0">
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
                            </div>
                            
                            <!-- Описание -->
                            <div class="mb-4 flex-1 min-h-0">
                                @if(filled(trim(strip_tags((string) ($team->description ?? '')))))
                                    <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($team->description))), 240) }}</p>
                                @else
                                    <p class="text-gray-400 text-sm italic">Описание отсутствует</p>
                                @endif
                            </div>

                            <!-- Кнопка подробнее -->
                            <div class="mt-auto">
                                <a 
                                    href="{{ route('teams.show', ['team' => $team]) }}" 
                                    class="block w-full text-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors text-sm font-medium shadow-sm"
                                >
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $teams->links('pagination::tailwind') }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет команд</h3>
                <p class="mt-1 text-sm text-gray-500">Команды пока не созданы.</p>
            </div>
        @endif
    </div>
@endsection

