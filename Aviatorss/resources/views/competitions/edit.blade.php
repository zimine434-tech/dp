@extends('layouts.teacher')

@section('title', 'Редактирование соревнования')

@section('content')
    <div class="max-w-4xl mx-auto overflow-visible">
        <div class="bg-white shadow-lg rounded-lg">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h1 class="text-xl sm:text-2xl font-bold">Редактирование соревнования</h1>
                <p class="text-blue-100 mt-1 text-sm sm:text-base">Обновите информацию о соревновании</p>
            </div>

            <!-- Форма -->
            <form action="{{ route('competitions.update', $competition) }}" method="POST" class="p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-visible">
                @csrf
                @method('PUT')

                <!-- Команда -->
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
                            <option value="{{ $teamOption->id }}" @selected((string) old('team_id', $competition->team_id) === (string) $teamOption->id)>
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
                        value="{{ old('name', $competition->name) }}"
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
                    >{{ old('description', $competition->description) }}</textarea>
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
                        value="{{ old('start_date', $competition->start_date->format('Y-m-d')) }}"
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
                        value="{{ old('end_date', $competition->end_date->format('Y-m-d')) }}"
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
                    <div class="relative overflow-visible">
                        <!-- Скрытое поле для формы -->
                        <input type="hidden" name="location_id" id="location_id" value="{{ old('location_id', $competition->location_id) }}" required>
                        
                        <!-- Кнопка выбора локации -->
                        <button 
                            type="button"
                            id="location-select-button"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location_id') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                            onclick="toggleLocationDropdown()"
                        >
                            <span id="location-select-text" class="text-gray-700">
                                @php
                                    $selectedLocation = $locations->firstWhere('id', old('location_id', $competition->location_id));
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
                                        class="location-option px-4 py-2 hover:bg-blue-50 cursor-pointer {{ old('location_id', $competition->location_id) == $location->id ? 'bg-blue-100' : '' }}"
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
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>

        <!-- Создание локации для соревнований -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h2 class="text-xl sm:text-2xl font-bold">Создание новой локации</h2>
                <p class="text-green-100 mt-1 text-sm sm:text-base">Если нужной локации нет в списке, создайте новую</p>
            </div>

            <!-- Форма создания локации -->
            <form id="create-location-form" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                @csrf

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

                <!-- Кнопка создания -->
                <div class="flex justify-end pt-4 border-t">
                    <button 
                        type="submit" 
                        class="px-4 sm:px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm sm:text-base"
                    >
                        Создать локацию
                    </button>
                </div>
            </form>

            <!-- Сообщение об успехе/ошибке -->
            <div id="location-message" class="hidden mx-4 sm:mx-6 mb-4"></div>
        </div>

        <!-- Форма создания категории для соревнований -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mt-6">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h2 class="text-xl sm:text-2xl font-bold">Создание категории</h2>
                <p class="text-purple-100 mt-1 text-sm sm:text-base">Создайте категорию для соревнования</p>
            </div>

            <!-- Форма создания категории -->
            <form id="create-category-form" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                @csrf

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

                <!-- Кнопка создания -->
                <div class="flex justify-end pt-4 border-t">
                    <button 
                        type="submit" 
                        class="px-4 sm:px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm sm:text-base"
                    >
                        Создать категорию
                    </button>
                </div>
            </form>

            <!-- Сообщение об успехе/ошибке -->
            <div id="category-message" class="hidden mx-4 sm:mx-6 mb-4"></div>
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

        // Создание категории
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
                    method: 'PUT',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeEditCategoryModal();
                    window.location.reload();
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

        // Закрытие модального окна при клике вне его
        document.getElementById('edit-category-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditCategoryModal();
            }
        });
    </script>
@endsection

