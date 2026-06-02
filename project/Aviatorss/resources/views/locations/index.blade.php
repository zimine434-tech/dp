@extends('layouts.teacher')

@section('title', 'Локации для соревнований')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('competitions.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">← К соревнованиям</a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Локации для соревнований</h1>
            </div>
            <a
                href="{{ route('competitions.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm"
            >
                Создание соревнования
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-6 py-4 text-white">
                        <h2 class="text-xl font-bold">Новая локация</h2>
                    </div>
                    <form id="create-location-form" class="px-4 sm:px-6 py-4 space-y-4">
                        @csrf
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
                                class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Название локации"
                            >
                            <p id="location-error" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                        <div>
                            <label for="location_address" class="block text-sm font-medium text-gray-700 mb-2">Адрес</label>
                            <input
                                type="text"
                                id="location_address"
                                name="address"
                                maxlength="255"
                                class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Адрес (необязательно)"
                            >
                        </div>
                        <div>
                            <label for="organizer" class="block text-sm font-medium text-gray-700 mb-2">Организатор</label>
                            <input
                                type="text"
                                id="organizer"
                                name="organizer"
                                maxlength="100"
                                class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Организатор (необязательно)"
                            >
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Создать локацию
                        </button>
                    </form>
                    <div id="location-message" class="hidden mx-4 sm:mx-6 mb-4"></div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Все локации для соревнований</h2>
                    <div id="locations-table-message" class="hidden mb-4"></div>
                    @if($locations->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Локация</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Адрес</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Организатор</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="locations-table-body" class="bg-white divide-y divide-gray-200">
                                    @foreach($locations as $location)
                                        <tr data-location-id="{{ $location->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $location->location }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $location->address ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $location->organizer ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                                <button
                                                    type="button"
                                                    onclick="editLocation({{ $location->id }}, @js($location->location), @js($location->address ?? ''), @js($location->organizer ?? ''))"
                                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                                >
                                                    Редактировать
                                                </button>
                                                <button
                                                    type="button"
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
                        <p id="locations-empty-message" class="text-gray-500">Пока нет локаций для соревнований.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="edit-location-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Редактирование локации</h3>
            <form id="edit-location-form" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-location-id">
                <div>
                    <label for="edit-location-name" class="block text-sm font-medium text-gray-700 mb-2">Локация <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-location-name" name="location" required maxlength="200" class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="edit-location-address" class="block text-sm font-medium text-gray-700 mb-2">Адрес</label>
                    <input type="text" id="edit-location-address" name="address" maxlength="255" class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="edit-location-organizer" class="block text-sm font-medium text-gray-700 mb-2">Организатор</label>
                    <input type="text" id="edit-location-organizer" name="organizer" maxlength="100" class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditLocationModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Отмена</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Сохранить</button>
                </div>
            </form>
            <p id="edit-location-error" class="mt-2 text-sm text-red-600 hidden"></p>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('create-location-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const messageDiv = document.getElementById('location-message');
            const errorDiv = document.getElementById('location-error');

            messageDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');
            submitButton.disabled = true;

            try {
                const response = await fetch('{{ route('locations.store') }}', {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.reload();
                } else {
                    const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка');
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');
                }
            } catch {
                errorDiv.textContent = 'Произошла ошибка при создании локации';
                errorDiv.classList.remove('hidden');
            } finally {
                submitButton.disabled = false;
            }
        });

        function editLocation(id, location, address, organizer) {
            document.getElementById('edit-location-id').value = id;
            document.getElementById('edit-location-name').value = location;
            document.getElementById('edit-location-address').value = address || '';
            document.getElementById('edit-location-organizer').value = organizer || '';
            document.getElementById('edit-location-error').classList.add('hidden');
            document.getElementById('edit-location-modal').classList.remove('hidden');
        }

        function closeEditLocationModal() {
            document.getElementById('edit-location-modal').classList.add('hidden');
            document.getElementById('edit-location-form').reset();
        }

        document.getElementById('edit-location-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const locationId = document.getElementById('edit-location-id').value;
            const errorDiv = document.getElementById('edit-location-error');
            errorDiv.classList.add('hidden');

            const formData = new FormData(this);
            formData.append('_token', csrfToken);

            try {
                const response = await fetch(`{{ url('/locations') }}/${locationId}`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка');
                    errorDiv.classList.remove('hidden');
                }
            } catch {
                errorDiv.textContent = 'Произошла ошибка при обновлении локации';
                errorDiv.classList.remove('hidden');
            }
        });

        document.getElementById('edit-location-modal').addEventListener('click', function(e) {
            if (e.target === this) closeEditLocationModal();
        });

        function deleteLocation(locationId) {
            if (!confirm('Вы уверены, что хотите удалить эту локацию?')) return;

            fetch(`{{ url('/locations') }}/${locationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr[data-location-id="${locationId}"]`);
                        if (row) row.remove();
                        showTableMessage('locations-table-message', data.message);
                    } else {
                        alert(data.message || 'Произошла ошибка при удалении локации');
                    }
                })
                .catch(() => alert('Произошла ошибка при удалении локации'));
        }

        function showTableMessage(elementId, message) {
            const messageDiv = document.getElementById(elementId);
            messageDiv.className = 'bg-green-50 border-l-4 border-green-400 p-4 rounded';
            messageDiv.innerHTML = `<p class="text-sm text-green-700">${message}</p>`;
            messageDiv.classList.remove('hidden');
            setTimeout(() => messageDiv.classList.add('hidden'), 3000);
        }
    </script>
@endsection
