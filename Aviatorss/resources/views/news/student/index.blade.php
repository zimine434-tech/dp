@extends('layouts.student')

@section('title', 'Новости')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Новости</h1>
        </div>

        <!-- Сообщения об ошибках -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Сообщения об успехе -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Сетка карточек (пагинация отдельно ниже) -->
        <div id="news-container">
            @if($publishedNews->count() > 0)
                @include('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'student'])

                @if($publishedNews->hasPages())
                    <div id="news-pagination" class="mt-8 border-t border-gray-200 pt-6">
                        {{ $publishedNews->links('pagination::tailwind') }}
                    </div>
                @endif
            @else
                <div class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center shadow-md">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Нет опубликованных новостей</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработка пагинации для новостей
            const newsPagination = document.getElementById('news-pagination');
            if (newsPagination) {
                newsPagination.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.href && !link.hasAttribute('data-turbo')) {
                        e.preventDefault();
                        loadPage(link.href);
                    }
                });
            }

            function loadPage(url) {
                // Показываем индикатор загрузки
                const container = document.getElementById('news-container');
                const originalContent = container.innerHTML;
                container.innerHTML = '<div class="p-12 text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-4 text-sm text-gray-500">Загрузка...</p></div>';

                // Создаем URL с параметрами пагинации
                const urlObj = new URL(url);
                
                // Добавляем параметр для AJAX-запроса
                urlObj.searchParams.set('ajax', '1');

                fetch(urlObj.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    let html = data.html;

                    if (data.pagination) {
                        html += '<div id="news-pagination" class="mt-8 border-t border-gray-200 pt-6">' + data.pagination + '</div>';
                    }
                    
                    container.innerHTML = html;
                    
                    // Переподключаем обработчик событий для пагинации
                    const paginationDiv = document.getElementById('news-pagination');
                    if (paginationDiv) {
                        paginationDiv.addEventListener('click', function(e) {
                            const link = e.target.closest('a');
                            if (link && link.href && !link.hasAttribute('data-turbo')) {
                                e.preventDefault();
                                loadPage(link.href);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Ошибка загрузки:', error);
                    container.innerHTML = originalContent;
                    alert('Произошла ошибка при загрузке страницы. Пожалуйста, попробуйте еще раз.');
                });
            }
        });
    </script>
@endsection

