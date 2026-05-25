@extends('layouts.teacher')

@section('title', 'Создание новости')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h1 class="text-xl sm:text-2xl font-bold">Создание новой новости</h1>
            </div>

            <!-- Форма -->
            <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                @csrf

                <!-- Информационное сообщение -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Важно:</strong> При создании новой новости она автоматически переносится в черновик. Не забудьте её опубликовать после создания.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Название новости -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Название новости <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        maxlength="200"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="Введите название новости"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание новости
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                        placeholder="Введите описание новости"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Фотографии -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                        Фотографии
                    </label>
                    <input
                        type="file"
                        id="images"
                        name="images[]"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                        class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('images') border border-red-500 rounded-lg @enderror @error('images.*') border border-red-500 rounded-lg @enderror"
                    >
                    <div id="images-preview" class="hidden mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Информация о дате -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm text-gray-600">
                            <strong>Дата:</strong> Автоматически будет установлена сегодняшняя дата ({{ now()->format('d.m.Y') }})
                        </p>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:space-x-4 pt-4 border-t">
                    <a
                        href="{{ route('news.index') }}"
                        class="px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center text-sm sm:text-base"
                    >
                        Отмена
                    </a>
                    <button
                        type="submit"
                        class="px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base"
                    >
                        Создать новость
                    </button>
                </div>
                @if (request()->boolean('upload_err'))
                    <p class="text-sm text-red-600 pt-2" role="alert">Превышен размер файла.</p>
                @endif
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('images');
            const container = document.getElementById('images-preview');
            if (!input || !container) return;
            let objectUrls = [];
            input.addEventListener('change', function () {
                objectUrls.forEach(function (u) { URL.revokeObjectURL(u); });
                objectUrls = [];
                container.innerHTML = '';
                container.classList.add('hidden');
                if (!input.files || !input.files.length) return;
                Array.from(input.files).forEach(function (file) {
                    if (!file.type.startsWith('image/')) return;
                    const url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    const wrap = document.createElement('div');
                    wrap.className = 'rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center min-h-[8rem]';
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = '';
                    img.className = 'max-w-full max-h-40 w-auto h-auto object-contain';
                    wrap.appendChild(img);
                    container.appendChild(wrap);
                });
                if (container.children.length) {
                    container.classList.remove('hidden');
                }
            });
        });
    </script>
@endpush
