@extends('layouts.teacher')

@section('title', 'Лента фотографий на главной')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Лента фотографий</h1>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Загрузить фото</h2>
            <form method="POST" action="{{ route('home-carousel-photos.store') }}" enctype="multipart/form-data" class="flex flex-wrap flex-col sm:flex-row gap-3 sm:items-end">
                @csrf
                <div class="flex-1 min-w-0">
                    <input
                        type="file"
                        name="photos[]"
                        accept=".jpg,.jpeg,.png,.webp,.gif,image/*"
                        multiple
                        required
                        class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100"
                    >
                </div>
                <button type="submit" class="inline-flex justify-center px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium whitespace-nowrap shrink-0">
                    Загрузить
                </button>
                <div class="w-full basis-full order-last space-y-1">
                    @if (request()->boolean('upload_err'))
                        <p class="text-sm text-red-600" role="alert">Превышен размер файла.</p>
                    @endif
                    @foreach ($errors->getMessages() as $key => $messages)
                        @if ($key === 'photos' || str_starts_with($key, 'photos.'))
                            @foreach ($messages as $message)
                                <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Текущие фото</h2>
                @if ($photos->isNotEmpty())
                    <p class="mt-1.5 text-xs text-gray-500">Перетащите карточки, чтобы изменить порядок на главной</p>
                @endif
            </div>

            @if ($photos->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
                    Пока нет ни одного снимка — загрузите файлы выше.
                </div>
            @else
                <ul id="home-carousel-sortable" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach ($photos as $photo)
                        <li
                            class="home-carousel-item group relative rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-grab active:cursor-grabbing shadow-sm"
                            data-filename="{{ $photo->filename }}"
                        >
                            <div class="relative aspect-square overflow-hidden bg-gray-900">
                                <img src="{{ $photo->url }}" alt="" class="h-full w-full object-cover pointer-events-none select-none">
                                <div class="absolute inset-x-0 bottom-0 flex justify-end bg-gradient-to-t from-black/60 via-black/25 to-transparent px-2 pb-2 pt-8">
                                    <form
                                        method="POST"
                                        action="{{ route('home-carousel-photos.destroy') }}"
                                        class="shrink-0"
                                        onsubmit="return confirm('Удалить это фото с главной страницы?');"
                                        onmousedown="event.stopPropagation()"
                                        ontouchstart="event.stopPropagation()"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="filename" value="{{ $photo->filename }}">
                                        <button type="submit" class="rounded-md bg-white/95 px-3 py-1.5 text-xs font-medium text-red-600 shadow-sm backdrop-blur-sm hover:bg-white hover:text-red-800">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection

@if ($photos->isNotEmpty())
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            (function () {
                var el = document.getElementById('home-carousel-sortable');
                if (!el || typeof Sortable === 'undefined') return;
                var csrf = document.querySelector('meta[name="csrf-token"]');
                var token = csrf ? csrf.getAttribute('content') : '';
                var url = @json(route('home-carousel-photos.order'));

                Sortable.create(el, {
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function () {
                        var order = [];
                        el.querySelectorAll('.home-carousel-item[data-filename]').forEach(function (node) {
                            order.push(node.getAttribute('data-filename'));
                        });
                        fetch(url, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ order: order })
                        }).catch(function () {});
                    }
                });
            })();
        </script>
    @endpush
@endif
