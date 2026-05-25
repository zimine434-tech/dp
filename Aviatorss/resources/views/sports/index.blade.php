@extends('layouts.teacher')

@section('title', 'Виды спорта')

@section('content')
    @php
        $sportsView = ($view ?? 'list') === 'cards' ? 'cards' : 'list';
        $sportsIndexBaseParams = array_filter([
            'view' => $sportsView === 'cards' ? 'cards' : null,
        ], fn ($v) => $v !== null && $v !== '');
    @endphp
    <div class="space-y-6">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Виды спорта</h1>
            </div>
            <a 
                href="{{ route('sports.create') }}" 
                class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="hidden sm:inline">Создать вид спорта</span>
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

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                <form
                    method="get"
                    action="{{ route('sports.index') }}"
                    class="flex h-full flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
                >
                    @if($sportsView === 'cards')
                        <input type="hidden" name="view" value="cards">
                    @endif
                    <div class="min-w-0 flex-1 sm:min-w-[12rem]">
                        <label for="sports_filter_q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Поиск по названию</label>
                        <input
                            id="sports_filter_q"
                            type="search"
                            name="q"
                            value="{{ $q ?? '' }}"
                            maxlength="255"
                            placeholder="Введите название…"
                            autocomplete="off"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                        @error('q')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                        @error('view')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                        <button
                            type="submit"
                            class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 sm:flex-none"
                        >
                            Найти
                        </button>
                        @if(($q ?? '') !== '')
                            <a
                                href="{{ route('sports.index', $sportsIndexBaseParams) }}"
                                class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-200 sm:flex-none"
                            >
                                Сбросить
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="flex shrink-0 flex-col">
                @include('sports.partials.view-toolbar', ['view' => $sportsView, 'q' => $q ?? ''])
            </div>
        </div>

        <!-- Список видов спорта -->
        @if($sports->count() > 0)
            @if($sportsView === 'cards')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($sports as $sport)
                        <div class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-blue-300 hover:shadow-md">
                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <h3 class="text-lg font-bold leading-tight text-gray-900 break-words">{{ $sport->name }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600">
                                    {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($sport->description ?? '')))), 220) ?: 'Описание отсутствует' }}
                                </p>
                                <p class="mt-3 text-xs text-gray-500 lg:text-sm">
                                    <span class="text-gray-500">Создатель:</span>
                                    <span class="font-medium text-gray-700">{{ $sport->creator ? $sport->creator->firstname . ' ' . $sport->creator->lastname : 'Не указан' }}</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 px-5 py-4 sm:px-6">
                                <a
                                    href="{{ route('sports.show', $sport) }}"
                                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-center text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 sm:flex-none"
                                >
                                    Подробнее
                                </a>
                                <a
                                    href="{{ route('sports.edit', $sport) }}"
                                    class="inline-flex flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-center text-sm font-medium text-gray-800 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none"
                                >
                                    Редактировать
                                </a>
                                <form
                                    action="{{ route('sports.destroy', $sport) }}"
                                    method="POST"
                                    class="inline w-full sm:w-auto sm:flex-none"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить этот вид спорта?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 sm:w-auto"
                                    >
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Описание</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Создатель</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sports as $sport)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $sport->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        <div class="text-sm text-gray-500 line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($sport->description ?? '')))), 220) ?: 'Описание отсутствует' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                        <div class="text-sm text-gray-500">
                                            {{ $sport->creator ? $sport->creator->firstname . ' ' . $sport->creator->lastname : 'Не указан' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a 
                                                href="{{ route('sports.show', $sport) }}" 
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Подробнее
                                            </a>
                                            <a 
                                                href="{{ route('sports.edit', $sport) }}" 
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Редактировать
                                            </a>
                                            <form 
                                                action="{{ route('sports.destroy', $sport) }}" 
                                                method="POST" 
                                                class="inline"
                                                onsubmit="return confirm('Вы уверены, что хотите удалить этот вид спорта?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="text-red-600 hover:text-red-900"
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
            </div>
            @endif

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $sports->links() }}
            </div>
        @elseif(($q ?? '') !== '')
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Ничего не найдено</h3>
                <p class="mt-1 text-sm text-gray-500">Попробуйте изменить запрос или сбросить поиск.</p>
                <div class="mt-6">
                    <a
                        href="{{ route('sports.index', $sportsIndexBaseParams) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium"
                    >
                        Сбросить поиск
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет видов спорта</h3>
                <p class="mt-1 text-sm text-gray-500">Начните с создания нового вида спорта.</p>
                <div class="mt-6">
                    <a 
                        href="{{ route('sports.create') }}" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Создать вид спорта
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

