@extends('layouts.teacher')

@section('title', 'Редактирование команды')

@php
    $fieldClass =
        'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25';
@endphp

@section('content')
    <div class="mx-auto max-w-3xl pb-64">
        <div class="overflow-visible rounded-lg bg-white shadow-lg">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h1 class="text-xl sm:text-2xl font-bold">Редактирование команды</h1>
                <p class="text-blue-100 mt-1 text-sm sm:text-base break-words">{{ $team->name }}</p>
            </div>

            <!-- Форма -->
            <form action="{{ route('teams.update', $team) }}" method="POST" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                @csrf
                @method('PUT')

                <!-- Название команды -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Название команды <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $team->name) }}"
                        required
                        class="{{ $fieldClass }} @error('name') border-red-500 ring-1 ring-red-200 @enderror"
                        placeholder="Введите название команды"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание команды
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        class="wysiwyg {{ $fieldClass }} @error('description') border-red-500 ring-1 ring-red-200 @enderror"
                        placeholder="Введите описание команды (необязательно)"
                    >{{ old('description', $team->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sport_id_combobox_trigger" class="block text-sm font-medium text-gray-700 mb-2">
                        Вид спорта
                    </label>
                    <x-teacher-sport-combobox
                        :sports="$sports"
                        :selected="old('sport_id', $team->sport_id)"
                        name="sport_id"
                    />
                </div>

                <!-- Кнопки -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:space-x-4 pt-4 border-t">
                    <a 
                        href="{{ route('teams.show', ['team' => $team]) }}" 
                        class="px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center text-sm sm:text-base"
                    >
                        Отмена
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base"
                    >
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

