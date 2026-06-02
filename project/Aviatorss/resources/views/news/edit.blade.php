@extends('layouts.teacher')

@section('title', 'Редактирование новости')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-6 text-white">
                <h1 class="text-xl sm:text-2xl font-bold">Редактирование новости</h1>
            </div>

            <!-- Форма -->
            <form action="{{ route('news.update', $news) }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                @csrf
                @method('PUT')

                <!-- Название новости -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Название новости <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $news->name) }}"
                        required
                        maxlength="200"
                        class="wysiwyg w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
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
                        placeholder="Введите описание новости (необязательно)"
                    >{{ old('description', $news->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Фотографии -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Фотографии
                    </label>
                    @if($news->images->isNotEmpty())
                        <div class="mb-4 space-y-3">
                            @foreach($news->images as $img)
                                <div class="flex flex-col sm:flex-row sm:items-start gap-3 p-3 rounded-lg border border-gray-200 bg-gray-50">
                                    <div class="shrink-0 w-full sm:w-32 h-32 rounded-lg overflow-hidden border border-gray-200 bg-white flex items-center justify-center">
                                        <img src="{{ $img->url }}" alt="" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0 text-sm">
                                        <label class="flex items-center gap-2 mt-3 text-gray-700">
                                            <input type="checkbox" name="remove_image_ids[]" value="{{ $img->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            Удалить это фото
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <label for="images" class="block text-sm text-gray-600 mb-1">Добавить новые файлы</label>
                    <input
                        type="file"
                        id="images"
                        name="images[]"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                        class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('images') border border-red-500 rounded-lg @enderror @error('images.*') border border-red-500 rounded-lg @enderror"
                    >
                    <div id="images-preview-new" class="hidden mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('remove_image_ids.*')
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
                        Сохранить изменения
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
            const container = document.getElementById('images-preview-new');
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
                    wrap.className = 'rounded-lg overflow-hidden border border-dashed border-blue-200 bg-blue-50/50 flex items-center justify-center min-h-[8rem]';
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
