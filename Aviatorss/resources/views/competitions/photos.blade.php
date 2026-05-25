@extends('layouts.teacher')

@section('title', 'Фотографии: '.$competition->name)

@section('content')
    @php
        $galleryUrls = ($images ?? collect())->map(fn ($i) => $i->url)->values()->all();
    @endphp
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Фотографии соревнования</h1>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">{{ $competition->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2 justify-end">
                <a
                    href="{{ route('competitions.results') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium whitespace-nowrap"
                >
                    К результатам
                </a>
                <a
                    href="{{ route('competitions.show', $competition) }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium whitespace-nowrap"
                >
                    Страница соревнования
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded" role="alert">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded" role="alert">
                <p class="text-sm font-medium text-red-800 mb-2">Не удалось загрузить</p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Загрузить фотографии</h2>
            <form action="{{ route('competitions.photos.store', $competition) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Файлы</label>
                    <input
                        type="file"
                        name="images[]"
                        id="images"
                        accept="image/*"
                        multiple
                        required
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    >
                </div>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                    Загрузить
                </button>
                @if (request()->boolean('upload_err'))
                    <p class="text-sm text-red-600 pt-2" role="alert">Превышен размер файла.</p>
                @endif
            </form>
        </div>

        @if ($images->isNotEmpty())
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h2 class="text-lg font-semibold text-gray-900">Уже загружено</h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach ($images as $img)
                            <div class="relative group rounded-lg border border-gray-100 overflow-hidden bg-gray-50">
                                <button
                                    type="button"
                                    class="block w-full aspect-video"
                                >
                                    <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover">
                                </button>
                                <form
                                    action="{{ route('competitions.photo-archive.destroy', $img) }}"
                                    method="POST"
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition"
                                    onsubmit="return confirm('Удалить это фото?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs font-medium bg-red-600 text-white rounded shadow hover:bg-red-700">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection


