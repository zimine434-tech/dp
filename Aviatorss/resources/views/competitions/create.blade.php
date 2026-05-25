@extends('layouts.teacher')

@section('title', 'Создание соревнования')

@section('content')
    <div class="max-w-7xl mx-auto overflow-visible">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 overflow-visible">
            <!-- Основная форма создания соревнования -->
            <div class="lg:col-span-2 overflow-visible">
                <div class="bg-white shadow-lg rounded-lg overflow-visible">
                    <!-- Заголовок -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                        <h1 class="text-xl sm:text-2xl font-bold">Создание соревнования</h1>
                        <p class="text-blue-100 mt-1 text-sm sm:text-base">Заполните форму для создания нового соревнования</p>
                    </div>

                    <!-- Форма -->
                    <form action="{{ route('competitions.store') }}" method="POST" class="p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-visible">
                @csrf

                <!-- Команда (вид спорта берётся из карточки команды) -->
                <div>
                    <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Команда <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="team_id"
                        name="team_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('team_id') border-red-500 @enderror"
                    >
                        <option value="">Выберите команду</option>
                        @foreach($teams as $teamOption)
                            <option value="{{ $teamOption->id }}" @selected((string) old('team_id') === (string) $teamOption->id)>
                                {{ $teamOption->name }}@if($teamOption->sport) — {{ $teamOption->sport->name }}@else (вид спорта не задан)@endif
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Название соревнования -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Название соревнования <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required
                        maxlength="200"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="Введите название соревнования"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание соревнования
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                        placeholder="Введите описание соревнования (необязательно)"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Дата начала -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Дата начала <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        value="{{ old('start_date') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror"
                    >
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Дата окончания -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Дата окончания <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        value="{{ old('end_date') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror"
                    >
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Локация -->
                <div>
                    <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Локация <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <!-- Скрытое поле для формы -->
                        <input type="hidden" name="location_id" id="location_id" value="{{ old('location_id') }}" required>
                        
                        <!-- Кнопка выбора локации -->
                        <button 
                            type="button"
                            id="location-select-button"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location_id') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                            onclick="toggleLocationDropdown()"
                        >
                            <span id="location-select-text" class="text-gray-700">
                                @php
                                    $selectedLocation = $locations->firstWhere('id', old('location_id'));
                                @endphp
                                @if($selectedLocation)
                                    {{ $selectedLocation->location }}@if($selectedLocation->organizer) - {{ $selectedLocation->organizer }}@endif
                                @else
                                    Выберите локацию
                                @endif
                            </span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown меню -->
                        <div id="location-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                            <!-- Поиск -->
                            <div class="p-2 border-b border-gray-200 flex-shrink-0">
                                <input 
                                    type="text" 
                                    id="location-search"
                                    placeholder="Поиск локации..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onkeyup="filterLocations(this.value)"
                                >
                            </div>
                            
                            <!-- Список локаций -->
                            <div id="location-list" class="overflow-y-auto flex-1 min-h-0">
                                @foreach($locations as $location)
                                    <div 
                                        class="location-option px-4 py-2 hover:bg-blue-50 cursor-pointer {{ old('location_id') == $location->id ? 'bg-blue-100' : '' }}"
                                        data-id="{{ $location->id }}"
                                        data-location="{{ $location->location }}"
                                        data-organizer="{{ $location->organizer ?? '' }}"
                                        onclick="selectLocation({{ $location->id }}, '{{ addslashes($location->location) }}', '{{ addslashes($location->organizer ?? '') }}')"
                                    >
                                        <div class="font-medium text-gray-900">{{ $location->location }}</div>
                                        @if($location->organizer)
                                            <div class="text-sm text-gray-500">{{ $location->organizer }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Сообщение "Ничего не найдено" -->
                            <div id="location-no-results" class="hidden px-4 py-2 text-sm text-gray-500 text-center">
                                Ничего не найдено
                            </div>
                        </div>
                    </div>
                    @error('location_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Категория -->
                <div>
                    <label for="competition_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Категория
                    </label>
                    <div class="relative">
                        <!-- Скрытое поле для формы -->
                        <input type="hidden" name="competition_category_id" id="competition_category_id" value="{{ old('competition_category_id') }}">
                        
                        <!-- Кнопка выбора категории -->
                        <button 
                            type="button"
                            id="category-select-button"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('competition_category_id') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                            onclick="toggleCategoryDropdown()"
                        >
                            <span id="category-select-text" class="text-gray-700">
                                @php
                                    $selectedCategory = isset($categories) ? $categories->firstWhere('id', old('competition_category_id')) : null;
                                @endphp
                                @if($selectedCategory)
                                    {{ $selectedCategory->name_category }}
                                @else
                                    Выберите категорию
                                @endif
                            </span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown меню -->
                        <div id="category-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg flex flex-col max-h-60">
                            <!-- Поиск -->
                            <div class="p-2 border-b border-gray-200 flex-shrink-0">
                                <input 
                                    type="text" 
                                    id="category-search"
                                    placeholder="Поиск категории..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onkeyup="filterCategories(this.value)"
                                >
                            </div>
                            
                            <!-- Список категорий -->
                            <div id="category-list" class="overflow-y-auto flex-1 min-h-0">
                                @if(isset($categories) && $categories->count() > 0)
                                    @foreach($categories as $category)
                                        <div 
                                            class="category-option px-4 py-2 hover:bg-blue-50 cursor-pointer {{ old('competition_category_id') == $category->id ? 'bg-blue-100' : '' }}"
                                            data-id="{{ $category->id }}"
                                            data-name="{{ $category->name_category }}"
                                            data-description="{{ $category->description ?? '' }}"
                                            onclick="selectCategory({{ $category->id }}, '{{ addslashes($category->name_category) }}', '{{ addslashes($category->description ?? '') }}')"
                                        >
                                            <div class="font-medium text-gray-900">{{ $category->name_category }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="px-4 py-2 text-sm text-gray-500 text-center">
                                        Нет доступных категорий
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Сообщение "Ничего не найдено" -->
                            <div id="category-no-results" class="hidden px-4 py-2 text-sm text-gray-500 text-center">
                                Ничего не найдено
                            </div>
                        </div>
                    </div>
                    @error('competition_category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Информация о статусе -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-600">
                            <strong>Статус:</strong> Автоматически будет установлен как "Предстоящее"
                        </p>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:space-x-4 pt-4 border-t">
                    <a 
                        href="{{ route('competitions.index') }}" 
                        class="px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center text-sm sm:text-base"
                    >
                        Отмена
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base"
                    >
                        Создать соревнование
                    </button>
                </div>
            </form>
            </div>
            </div>

            <!-- Форма создания локации для соревнований -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden h-fit self-start">
                <!-- Заголовок -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                    <h2 class="text-xl sm:text-2xl font-bold">Создание новой локации</h2>
                    <p class="text-green-100 mt-1 text-sm sm:text-base">Если нужной локации нет в списке, создайте новую</p>
                </div>

                <!-- Форма создания локации -->
                <form id="create-location-form" class="px-4 sm:px-6 pt-4 sm:pt-6">
                    @csrf

                    <div class="space-y-4 sm:space-y-6">
                        <!-- Локация -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                Локация <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="location" 
                                name="location" 
                                required
                                maxlength="200"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Введите название локации (например: Стадион, Спортзал, Площадка)"
                            >
                            <p id="location-error" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Организатор -->
                        <div>
                            <label for="organizer" class="block text-sm font-medium text-gray-700 mb-2">
                                Организатор
                            </label>
                            <input 
                                type="text" 
                                id="organizer" 
                                name="organizer" 
                                maxlength="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Введите название организатора (необязательно)"
                            >
                        </div>
                    </div>

                    <!-- Кнопка создания -->
                    <div class="flex justify-center items-center border-t mt-4 sm:mt-6 min-h-[60px]">
                        <button 
                            type="submit" 
                            class="px-4 sm:px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm sm:text-base"
                        >
                            Создать локацию
                        </button>
                    </div>
                </form>

                <!-- Сообщение об успехе/ошибке -->
                <div id="location-message" class="hidden mx-4 sm:mx-6"></div>
            </div>

            <!-- Форма создания категории для соревнований -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden h-fit self-start mt-6">
                <!-- Заголовок -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                    <h2 class="text-xl sm:text-2xl font-bold">Создание категории</h2>
                    <p class="text-purple-100 mt-1 text-sm sm:text-base">Если нужной категории нет в списке, создайте новую</p>
                </div>

                <!-- Форма создания категории -->
                <form id="create-category-form" class="px-4 sm:px-6 pt-4 sm:pt-6">
                    @csrf

                    <div class="space-y-4 sm:space-y-6">
                        <!-- Название категории -->
                        <div>
                            <label for="category-name" class="block text-sm font-medium text-gray-700 mb-2">
                                Название категории <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="category-name" 
                                name="name_category" 
                                required
                                maxlength="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Введите название категории"
                            >
                            <p id="category-name-error" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Описание категории -->
                        <div>
                            <label for="category-description" class="block text-sm font-medium text-gray-700 mb-2">
                                Описание категории
                            </label>
                            <input 
                                type="text" 
                                id="category-description" 
                                name="description" 
                                maxlength="45"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Введите описание категории (необязательно)"
                            >
                        </div>
                    </div>

                    <!-- Кнопка создания -->
                    <div class="flex justify-center items-center border-t mt-4 sm:mt-6 min-h-[60px]">
                        <button 
                            type="submit" 
                            class="px-4 sm:px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm sm:text-base"
                            id="create-category-button"
                        >
                            Создать категорию
                        </button>
                    </div>
                </form>

                <!-- Сообщение об успехе/ошибке -->
                <div id="category-message" class="hidden mx-4 sm:mx-6"></div>
            </div>
        </div>

        <!-- Все локации для соревнований -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Все локации для соревнований</h2>
            <div id="locations-table-message" class="hidden mb-4"></div>
                @if($locations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Локация</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Организатор</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($locations as $location)
                                    <tr data-location-id="{{ $location->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $location->location }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $location->organizer ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                            <button 
                                                onclick="editLocation({{ $location->id }}, '{{ addslashes($location->location) }}', '{{ addslashes($location->organizer ?? '') }}')"
                                                class="text-blue-600 hover:text-blue-900 mr-3"
                                            >
                                                Редактировать
                                            </button>
                                            <button 
                                                onclick="deleteLocation({{ $location->id }})"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Удалить
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">Пока нет локаций для соревнований.</p>
                @endif
        </div>

        <!-- Все категории для соревнований -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Все категории для соревнований</h2>
            <div id="categories-table-message" class="hidden mb-4"></div>
            @if(isset($categories) && $categories->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название категории</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Описание</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($categories as $category)
                                <tr data-category-id="{{ $category->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name_category }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $category->description ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                        <button 
                                            onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name_category) }}', '{{ addslashes($category->description ?? '') }}')"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            Редактировать
                                        </button>
                                        <button 
                                            onclick="deleteCategory({{ $category->id }})"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Удалить
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">Пока нет категорий для соревнований.</p>
            @endif
        </div>
    </div>

    <!-- Модальное окно редактирования категории -->
    <div id="edit-category-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Редактирование категории</h3>
                <form id="edit-category-form" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-category-id" name="id">
                    
                    <div>
                        <label for="edit-category-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Название категории <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit-category-name" 
                            name="name_category" 
                            required
                            maxlength="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        >
                    </div>

                    <div>
                        <label for="edit-category-description" class="block text-sm font-medium text-gray-700 mb-2">
                            Описание категории
                        </label>
                        <input 
                            type="text" 
                            id="edit-category-description" 
                            name="description" 
                            maxlength="45"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        >
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button 
                            type="button"
                            onclick="closeEditCategoryModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                        >
                            Сохранить
                        </button>
                    </div>
                </form>
                <p id="edit-category-error" class="mt-2 text-sm text-red-600 hidden"></p>
            </div>
        </div>
    </div>

    <!-- Модальное окно редактирования локации для соревнований -->
    <div id="edit-location-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Редактирование локации</h3>
                <form id="edit-location-form" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-location-id" name="id">
                    
                    <div>
                        <label for="edit-location-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Локация <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit-location-name" 
                            name="location" 
                            required
                            maxlength="200"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        >
                    </div>

                    <div>
                        <label for="edit-location-organizer" class="block text-sm font-medium text-gray-700 mb-2">
                            Организатор
                        </label>
                        <input 
                            type="text" 
                            id="edit-location-organizer" 
                            name="organizer" 
                            maxlength="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        >
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button 
                            type="button"
                            onclick="closeEditLocationModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                        >
                            Сохранить
                        </button>
                    </div>
                </form>
                <p id="edit-location-error" class="mt-2 text-sm text-red-600 hidden"></p>
            </div>
        </div>
    </div>

    <script>
        // Автоматическое обновление минимального значения для end_date при изменении start_date
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');
            if (startDate) {
                endDateInput.min = startDate;
                
                // Если end_date меньше start_date, обновляем его
                if (endDateInput.value && endDateInput.value < startDate) {
                    endDateInput.value = startDate;
                }
            }
        });


        // Функции для работы с dropdown локаций
        function toggleLocationDropdown() {
            const dropdown = document.getElementById('location-dropdown');
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    document.getElementById('location-search').focus();
                }, 100);
            }
        }

        function selectLocation(id, location, organizer) {
            const hiddenInput = document.getElementById('location_id');
            const buttonText = document.getElementById('location-select-text');
            const dropdown = document.getElementById('location-dropdown');
            
            hiddenInput.value = id;
            buttonText.textContent = location + (organizer ? ' - ' + organizer : '');
            
            document.querySelectorAll('.location-option').forEach(option => {
                option.classList.remove('bg-blue-100');
                if (option.getAttribute('data-id') == id) {
                    option.classList.add('bg-blue-100');
                }
            });
            
            dropdown.classList.add('hidden');
        }

        function filterLocations(searchValue) {
            const searchTerm = searchValue.toLowerCase().trim();
            const options = document.querySelectorAll('.location-option');
            const noResults = document.getElementById('location-no-results');
            let visibleCount = 0;

            options.forEach(option => {
                const location = option.getAttribute('data-location').toLowerCase();
                const organizer = option.getAttribute('data-organizer').toLowerCase();
                const fullText = (location + ' ' + organizer).trim();

                if (searchTerm === '' || location.includes(searchTerm) || organizer.includes(searchTerm) || fullText.includes(searchTerm)) {
                    option.style.display = '';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });

            if (visibleCount === 0 && searchTerm !== '') {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // Функции для работы с dropdown статуса
        function toggleStatusDropdown() {
            const dropdown = document.getElementById('status-dropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectStatus(value, label) {
            const hiddenInput = document.getElementById('status');
            const buttonText = document.getElementById('status-select-text');
            const dropdown = document.getElementById('status-dropdown');
            
            hiddenInput.value = value;
            buttonText.textContent = label;
            
            document.querySelectorAll('.status-option').forEach(option => {
                option.classList.remove('bg-blue-100');
                if (option.getAttribute('data-value') == value) {
                    option.classList.add('bg-blue-100');
                }
            });
            
            dropdown.classList.add('hidden');
        }

        // Закрытие dropdown при клике вне его
        document.addEventListener('click', function(event) {
            const locationDropdown = document.getElementById('location-dropdown');
            const locationButton = document.getElementById('location-select-button');
            const statusDropdown = document.getElementById('status-dropdown');
            const statusButton = document.getElementById('status-select-button');

            // Закрываем dropdown локаций
            if (locationDropdown && locationButton) {
                if (!locationDropdown.contains(event.target) && !locationButton.contains(event.target)) {
                    locationDropdown.classList.add('hidden');
                }
            }
            
            // Закрываем dropdown статуса
            if (statusDropdown && statusButton) {
                if (!statusDropdown.contains(event.target) && !statusButton.contains(event.target)) {
                    statusDropdown.classList.add('hidden');
                }
            }
            
            // Закрываем dropdown категорий
            const categoryDropdown = document.getElementById('category-dropdown');
            const categoryButton = document.getElementById('category-select-button');
            if (categoryDropdown && categoryButton) {
                if (!categoryDropdown.contains(event.target) && !categoryButton.contains(event.target)) {
                    categoryDropdown.classList.add('hidden');
                }
            }
        });

        // Создание новой локации
        document.getElementById('create-location-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const messageDiv = document.getElementById('location-message');
            const errorDiv = document.getElementById('location-error');

            // Очищаем предыдущие сообщения
            messageDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            // Получаем данные формы
            const formData = new FormData(form);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Отключаем кнопку
            submitButton.disabled = true;
            submitButton.textContent = 'Создание...';

            try {
                const response = await fetch('{{ route("locations.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Показываем сообщение об успехе
                    messageDiv.className = 'mx-4 sm:mx-6 mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">${data.message}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');

                    // Добавляем новую локацию в dropdown
                    const locationList = document.getElementById('location-list');
                    const newOption = document.createElement('div');
                    newOption.className = 'location-option px-4 py-2 hover:bg-blue-50 cursor-pointer bg-blue-100';
                    newOption.setAttribute('data-id', data.location.id);
                    newOption.setAttribute('data-location', data.location.location);
                    newOption.setAttribute('data-organizer', data.location.organizer || '');
                    newOption.innerHTML = `
                        <div class="font-medium text-gray-900">${data.location.location}</div>
                        ${data.location.organizer ? '<div class="text-sm text-gray-500">' + data.location.organizer + '</div>' : ''}
                    `;
                    newOption.onclick = function() {
                        selectLocation(data.location.id, data.location.location, data.location.organizer || '');
                    };
                    locationList.appendChild(newOption);
                    
                    // Автоматически выбираем новую локацию
                    selectLocation(data.location.id, data.location.location, data.location.organizer || '');

                    // Очищаем форму
                    form.reset();

                    // Скрываем сообщение через 3 секунды
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    // Показываем ошибку
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка при создании локации');
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');

                    messageDiv.className = 'mx-4 sm:mx-6 mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">${errorMessage}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                errorDiv.textContent = 'Произошла ошибка при создании локации';
                errorDiv.classList.remove('hidden');

                messageDiv.className = 'mx-4 sm:mx-6 mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded';
                messageDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">Произошла ошибка при создании локации</p>
                        </div>
                    </div>
                `;
                messageDiv.classList.remove('hidden');
            } finally {
                // Включаем кнопку обратно
                submitButton.disabled = false;
                submitButton.textContent = 'Создать локацию';
            }
        });

        // Функции для редактирования локации
        function editLocation(id, location, organizer) {
            document.getElementById('edit-location-id').value = id;
            document.getElementById('edit-location-name').value = location;
            document.getElementById('edit-location-organizer').value = organizer || '';
            document.getElementById('edit-location-modal').classList.remove('hidden');
            document.getElementById('edit-location-error').classList.add('hidden');
        }

        function closeEditLocationModal() {
            document.getElementById('edit-location-modal').classList.add('hidden');
            document.getElementById('edit-location-form').reset();
        }

        // Обработка формы редактирования локации
        document.getElementById('edit-location-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const locationId = document.getElementById('edit-location-id').value;
            const errorDiv = document.getElementById('edit-location-error');

            errorDiv.classList.add('hidden');

            const formData = new FormData(form);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const response = await fetch(`{{ url('/locations') }}/${locationId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeEditLocationModal();
                    window.location.reload();
                } else {
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка при обновлении локации');
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                errorDiv.textContent = 'Произошла ошибка при обновлении локации';
                errorDiv.classList.remove('hidden');
            }
        });

        // Закрытие модального окна при клике вне его
        document.getElementById('edit-location-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditLocationModal();
            }
        });

        // Удаление локации
        function deleteLocation(locationId) {
            if (!confirm('Вы уверены, что хотите удалить эту локацию?')) {
                return;
            }

            fetch(`/locations/${locationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем строку из таблицы
                    const row = document.querySelector(`tr[data-location-id="${locationId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Удаляем из dropdown
                    const option = document.querySelector(`.location-option[data-id="${locationId}"]`);
                    if (option) {
                        option.remove();
                    }

                    // Показываем сообщение об успехе
                    const messageDiv = document.getElementById('locations-table-message');
                    messageDiv.className = 'bg-green-50 border-l-4 border-green-400 p-4 rounded';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">${data.message}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');

                    // Скрываем сообщение через 3 секунды
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    alert(data.message || 'Произошла ошибка при удалении локации');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении локации');
            });
        }

        // Функции для редактирования категории
        function editCategory(id, name, description) {
            document.getElementById('edit-category-id').value = id;
            document.getElementById('edit-category-name').value = name;
            document.getElementById('edit-category-description').value = description || '';
            document.getElementById('edit-category-modal').classList.remove('hidden');
            document.getElementById('edit-category-error').classList.add('hidden');
        }

        function closeEditCategoryModal() {
            document.getElementById('edit-category-modal').classList.add('hidden');
            document.getElementById('edit-category-form').reset();
        }

        // Обработка формы редактирования категории
        document.getElementById('edit-category-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const categoryId = document.getElementById('edit-category-id').value;
            const errorDiv = document.getElementById('edit-category-error');

            errorDiv.classList.add('hidden');

            const formData = new FormData(form);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const response = await fetch(`/competition-categories/${categoryId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeEditCategoryModal();
                    
                    // Обновляем строку в таблице
                    const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                    if (row) {
                        row.querySelector('td:first-child').textContent = data.category.name_category;
                        const descriptionCell = row.querySelector('td:nth-child(2)');
                        if (descriptionCell) {
                            descriptionCell.textContent = data.category.description || '-';
                        }
                    }
                    
                    // Обновляем dropdown
                    const option = document.querySelector(`.category-option[data-id="${categoryId}"]`);
                    if (option) {
                        option.setAttribute('data-name', data.category.name_category);
                        option.setAttribute('data-description', data.category.description || '');
                        option.querySelector('.font-medium').textContent = data.category.name_category;
                        option.setAttribute('onclick', `selectCategory(${categoryId}, '${data.category.name_category.replace(/'/g, "\\'")}', '${(data.category.description || '').replace(/'/g, "\\'")}')`);
                    }

                    // Показываем сообщение об успехе
                    const messageDiv = document.getElementById('categories-table-message');
                    messageDiv.className = 'bg-green-50 border-l-4 border-green-400 p-4 rounded mb-4';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">${data.message}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');

                    // Скрываем сообщение через 3 секунды
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка при обновлении категории');
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                errorDiv.textContent = 'Произошла ошибка при обновлении категории';
                errorDiv.classList.remove('hidden');
            }
        });

        // Закрытие модального окна редактирования категории при клике вне его
        document.getElementById('edit-category-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditCategoryModal();
            }
        });

        // Удаление категории
        function deleteCategory(categoryId) {
            if (!confirm('Вы уверены, что хотите удалить эту категорию?')) {
                return;
            }

            fetch(`/competition-categories/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем строку из таблицы
                    const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Удаляем из dropdown
                    const option = document.querySelector(`.category-option[data-id="${categoryId}"]`);
                    if (option) {
                        option.remove();
                    }

                    // Показываем сообщение об успехе
                    const messageDiv = document.getElementById('categories-table-message');
                    messageDiv.className = 'bg-green-50 border-l-4 border-green-400 p-4 rounded';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">${data.message}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');

                    // Скрываем сообщение через 3 секунды
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    alert(data.message || 'Произошла ошибка при удалении категории');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении категории');
            });
        }

        // Функции для работы с dropdown категорий
        function toggleCategoryDropdown() {
            const dropdown = document.getElementById('category-dropdown');
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    document.getElementById('category-search').focus();
                }, 100);
            }
        }

        function selectCategory(id, name, description) {
            const hiddenInput = document.getElementById('competition_category_id');
            const buttonText = document.getElementById('category-select-text');
            const dropdown = document.getElementById('category-dropdown');
            
            hiddenInput.value = id;
            buttonText.textContent = name;
            
            document.querySelectorAll('.category-option').forEach(option => {
                option.classList.remove('bg-blue-100');
                if (option.getAttribute('data-id') == id) {
                    option.classList.add('bg-blue-100');
                }
            });
            
            dropdown.classList.add('hidden');
        }

        function filterCategories(searchValue) {
            const searchTerm = searchValue.toLowerCase().trim();
            const options = document.querySelectorAll('.category-option');
            const noResults = document.getElementById('category-no-results');
            let visibleCount = 0;

            options.forEach(option => {
                const name = option.getAttribute('data-name').toLowerCase();
                const description = option.getAttribute('data-description').toLowerCase();
                const fullText = (name + ' ' + description).trim();

                if (searchTerm === '' || name.includes(searchTerm) || description.includes(searchTerm) || fullText.includes(searchTerm)) {
                    option.style.display = '';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });

            if (visibleCount === 0 && searchTerm !== '') {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // Создание категории (работает как создание локации)
        document.getElementById('create-category-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const messageDiv = document.getElementById('category-message');
            const errorDiv = document.getElementById('category-name-error');

            // Очищаем предыдущие сообщения
            messageDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            // Получаем данные формы
            const formData = new FormData(form);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Отключаем кнопку
            submitButton.disabled = true;
            submitButton.textContent = 'Создание...';

            try {
                const response = await fetch('{{ route("competition-categories.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Показываем сообщение об успехе
                    messageDiv.className = 'mx-4 sm:mx-6 mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">${data.message}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');

                    // Добавляем новую категорию в dropdown
                    const categoryList = document.getElementById('category-list');
                    // Удаляем сообщение "Нет доступных категорий" если оно есть
                    const noCategoriesMsg = categoryList.querySelector('.text-gray-500');
                    if (noCategoriesMsg && noCategoriesMsg.textContent.includes('Нет доступных категорий')) {
                        noCategoriesMsg.remove();
                    }
                    
                    const newOption = document.createElement('div');
                    newOption.className = 'category-option px-4 py-2 hover:bg-blue-50 cursor-pointer';
                    newOption.setAttribute('data-id', data.category.id);
                    newOption.setAttribute('data-name', data.category.name_category);
                    newOption.setAttribute('data-description', data.category.description || '');
                    newOption.innerHTML = `
                        <div class="font-medium text-gray-900">${data.category.name_category}</div>
                    `;
                    newOption.onclick = function() {
                        selectCategory(data.category.id, data.category.name_category, data.category.description || '');
                    };
                    categoryList.appendChild(newOption);
                    
                    // Автоматически выбираем новую категорию
                    selectCategory(data.category.id, data.category.name_category, data.category.description || '');

                    // Очищаем форму
                    form.reset();

                    // Скрываем сообщение через 3 секунды
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    // Показываем ошибку
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка при создании категории');
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');

                    messageDiv.className = 'mx-4 sm:mx-6 mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded';
                    messageDiv.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">${errorMessage}</p>
                            </div>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                errorDiv.textContent = 'Произошла ошибка при создании категории';
                errorDiv.classList.remove('hidden');

                messageDiv.className = 'mx-4 sm:mx-6 mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded';
                messageDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">Произошла ошибка при создании категории</p>
                        </div>
                    </div>
                `;
                messageDiv.classList.remove('hidden');
            } finally {
                // Включаем кнопку обратно
                submitButton.disabled = false;
                submitButton.textContent = 'Создать категорию';
            }
        });


        // Перехватываем отправку формы создания соревнования, чтобы получить ID созданного соревнования
        const competitionForm = document.querySelector('form[action="{{ route('competitions.store') }}"]');
        if (competitionForm) {
            competitionForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const form = this;
                const formData = new FormData(form);

                try {
                    const response = await fetch('{{ route('competitions.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.competition_id) {
                            // Перенаправляем на страницу соревнований
                            window.location.href = '{{ route('competitions.index') }}';
                        }
                    } else {
                        // Если ошибка, отправляем форму обычным способом
                        form.submit();
                    }
                } catch (error) {
                    // Если ошибка, отправляем форму обычным способом
                    form.submit();
                }
            });
        }
    </script>
@endsection

