@extends('layouts.teacher')

@section('title', 'Категории соревнований')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('competitions.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">← К соревнованиям</a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Категории для соревнований</h1>
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
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-4 sm:px-6 py-4 text-white">
                        <h2 class="text-xl font-bold">Новая категория</h2>
                    </div>
                    <form id="create-category-form" class="px-4 sm:px-6 py-4 space-y-4">
                        @csrf
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
                                class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Название категории"
                            >
                            <p id="category-name-error" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                        <div>
                            <label for="category-description" class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                            <input
                                type="text"
                                id="category-description"
                                name="description"
                                maxlength="45"
                                class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Описание (необязательно)"
                            >
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Создать категорию
                        </button>
                    </form>
                    <div id="category-message" class="hidden mx-4 sm:mx-6 mb-4"></div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Все категории для соревнований</h2>
                    <div id="categories-table-message" class="hidden mb-4"></div>
                    @if($categories->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название категории</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Описание</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="categories-table-body" class="bg-white divide-y divide-gray-200">
                                    @foreach($categories as $category)
                                        <tr data-category-id="{{ $category->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name_category }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $category->description ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                                <button
                                                    type="button"
                                                    onclick="editCategory({{ $category->id }}, @js($category->name_category), @js($category->description ?? ''))"
                                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                                >
                                                    Редактировать
                                                </button>
                                                <button
                                                    type="button"
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
                        <p id="categories-empty-message" class="text-gray-500">Пока нет категорий для соревнований.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="edit-category-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Редактирование категории</h3>
            <form id="edit-category-form" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-category-id">
                <div>
                    <label for="edit-category-name" class="block text-sm font-medium text-gray-700 mb-2">Название <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-category-name" name="name_category" required maxlength="100" class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label for="edit-category-description" class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                    <input type="text" id="edit-category-description" name="description" maxlength="45" class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditCategoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Отмена</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Сохранить</button>
                </div>
            </form>
            <p id="edit-category-error" class="mt-2 text-sm text-red-600 hidden"></p>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('create-category-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('category-name-error');

            errorDiv.classList.add('hidden');
            submitButton.disabled = true;

            try {
                const response = await fetch('{{ route('competition-categories.store') }}', {
                    method: 'POST',
                    body: new FormData(form),
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
                errorDiv.textContent = 'Произошла ошибка при создании категории';
                errorDiv.classList.remove('hidden');
            } finally {
                submitButton.disabled = false;
            }
        });

        function editCategory(id, name, description) {
            document.getElementById('edit-category-id').value = id;
            document.getElementById('edit-category-name').value = name;
            document.getElementById('edit-category-description').value = description || '';
            document.getElementById('edit-category-error').classList.add('hidden');
            document.getElementById('edit-category-modal').classList.remove('hidden');
        }

        function closeEditCategoryModal() {
            document.getElementById('edit-category-modal').classList.add('hidden');
            document.getElementById('edit-category-form').reset();
        }

        document.getElementById('edit-category-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const categoryId = document.getElementById('edit-category-id').value;
            const errorDiv = document.getElementById('edit-category-error');
            errorDiv.classList.add('hidden');

            const formData = new FormData(this);
            formData.append('_token', csrfToken);

            try {
                const response = await fetch(`{{ url('/competition-categories') }}/${categoryId}`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                    if (row) {
                        row.querySelector('td:first-child').textContent = data.category.name_category;
                        row.querySelector('td:nth-child(2)').textContent = data.category.description || '-';
                    }
                    closeEditCategoryModal();
                    showTableMessage('categories-table-message', data.message);
                } else {
                    errorDiv.textContent = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Произошла ошибка');
                    errorDiv.classList.remove('hidden');
                }
            } catch {
                errorDiv.textContent = 'Произошла ошибка при обновлении категории';
                errorDiv.classList.remove('hidden');
            }
        });

        document.getElementById('edit-category-modal').addEventListener('click', function(e) {
            if (e.target === this) closeEditCategoryModal();
        });

        function deleteCategory(categoryId) {
            if (!confirm('Вы уверены, что хотите удалить эту категорию?')) return;

            fetch(`{{ url('/competition-categories') }}/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                        if (row) row.remove();
                        showTableMessage('categories-table-message', data.message);
                    } else {
                        alert(data.message || 'Произошла ошибка при удалении категории');
                    }
                })
                .catch(() => alert('Произошла ошибка при удалении категории'));
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
