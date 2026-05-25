@extends('layouts.teacher')

@section('title', 'Редактирование тренировочной сессии')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h1 class="text-xl sm:text-2xl font-bold">Редактирование тренировочной сессии</h1>
                <p class="text-blue-100 mt-1 text-sm sm:text-base">Обновите информацию о тренировочной сессии</p>
            </div>

            <!-- Форма -->
            <form action="{{ route('training-sessions.update', $trainingSession) }}" method="POST" class="p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-visible">
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
                            <option value="{{ $teamOption->id }}" @selected((string) old('team_id', $trainingSession->team_id) === (string) $teamOption->id)>
                                {{ $teamOption->name }}@if($teamOption->sport) — {{ $teamOption->sport->name }}@else (вид спорта не задан)@endif
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Название тренировки -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Название тренировки <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="{{ old('title', $trainingSession->title) }}"
                        required
                        maxlength="200"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                        placeholder="Введите название тренировочной сессии"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание тренировки
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                        placeholder="Введите описание тренировочной сессии (необязательно)"
                    >{{ old('description', $trainingSession->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Дата и время начала -->
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Дата и время начала <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        id="start_time" 
                        name="start_time" 
                        value="{{ old('start_time', $trainingSession->start_time->format('Y-m-d\TH:i')) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_time') border-red-500 @enderror"
                    >
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Дата и время окончания -->
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Дата и время окончания <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        id="end_time" 
                        name="end_time" 
                        value="{{ old('end_time', $trainingSession->end_time->format('Y-m-d\TH:i')) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_time') border-red-500 @enderror"
                    >
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Локация -->
                <div>
                    <label for="locations_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Локация <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <!-- Скрытое поле для формы -->
                        <input type="hidden" name="locations_id" id="locations_id" value="{{ old('locations_id', $trainingSession->locations_id) }}" required>
                        
                        <!-- Кнопка выбора локации -->
                        <button 
                            type="button"
                            id="location-select-button"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('locations_id') border-red-500 @enderror bg-white text-left flex items-center justify-between"
                            onclick="toggleLocationDropdown()"
                        >
                            <span id="location-select-text" class="text-gray-700">
                                @php
                                    $selectedLocation = $locations->firstWhere('id', old('locations_id', $trainingSession->locations_id));
                                @endphp
                                @if($selectedLocation)
                                    {{ $selectedLocation->location }}@if($selectedLocation->description) - {{ $selectedLocation->description }}@endif
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
                                        class="location-option px-4 py-2 hover:bg-blue-50 cursor-pointer {{ old('locations_id', $trainingSession->locations_id) == $location->id ? 'bg-blue-100' : '' }}"
                                        data-id="{{ $location->id }}"
                                        data-location="{{ $location->location }}"
                                        data-description="{{ $location->description ?? '' }}"
                                        onclick="selectLocation({{ $location->id }}, '{{ addslashes($location->location) }}', '{{ addslashes($location->description ?? '') }}')"
                                    >
                                        <div class="font-medium text-gray-900">{{ $location->location }}</div>
                                        @if($location->description)
                                            <div class="text-sm text-gray-500">{{ $location->description }}</div>
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
                    @error('locations_id')
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
                            <strong>Статус:</strong> Автоматически будет установлен как "Запланирована"
                        </p>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:space-x-4 pt-4 border-t">
                    <a 
                        href="{{ route('training-sessions.index') }}" 
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

    <script>
        // Автоматическое обновление минимального значения для end_time при изменении start_time
        document.getElementById('start_time').addEventListener('change', function() {
            const startTime = this.value;
            const endTimeInput = document.getElementById('end_time');
            if (startTime) {
                // Устанавливаем минимальное значение end_time на 1 минуту после start_time
                const startDate = new Date(startTime);
                startDate.setMinutes(startDate.getMinutes() + 1);
                endTimeInput.min = startDate.toISOString().slice(0, 16);
                
                // Если end_time меньше нового минимума, обновляем его
                if (endTimeInput.value && new Date(endTimeInput.value) <= startDate) {
                    endTimeInput.value = startDate.toISOString().slice(0, 16);
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

        function selectLocation(id, location, description) {
            const hiddenInput = document.getElementById('locations_id');
            const buttonText = document.getElementById('location-select-text');
            const dropdown = document.getElementById('location-dropdown');
            
            hiddenInput.value = id;
            buttonText.textContent = location + (description ? ' - ' + description : '');
            
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
                const description = option.getAttribute('data-description').toLowerCase();
                const fullText = (location + ' ' + description).trim();

                if (searchTerm === '' || location.includes(searchTerm) || description.includes(searchTerm) || fullText.includes(searchTerm)) {
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

        // Закрытие dropdown при клике вне его
        document.addEventListener('click', function(event) {
            const locationDropdown = document.getElementById('location-dropdown');
            const locationButton = document.getElementById('location-select-button');

            if (locationDropdown && !locationDropdown.contains(event.target) && locationButton && !locationButton.contains(event.target)) {
                locationDropdown.classList.add('hidden');
            }
        });
    </script>
@endsection

