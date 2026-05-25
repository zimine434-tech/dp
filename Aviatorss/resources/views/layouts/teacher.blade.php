<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Панель преподавателя') - Авиатор</title>
    
    <meta name="description" content="@yield('description', 'Панель управления для преподавателей')">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @stack('styles')
    @yield('head')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Боковое меню -->
        <aside id="sidebar" class="w-64 bg-white shadow-lg flex flex-col h-dvh fixed z-50 -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static">
            <div class="p-4 border-b flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-800">Панель преподавателя</h1>
                <button type="button" id="toggleSidebar" class="p-2 rounded-lg hover:bg-gray-100 transition lg:hidden">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav class="mt-4 flex-1 overflow-y-auto">
                <ul class="space-y-1 px-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Главная
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Профиль
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('competitions.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Соревнования
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('competitions.results') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Результаты соревнований
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home-carousel-photos.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Лента фотографий
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('training-sessions.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            Тренировки
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('news.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            Новости
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teams.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Команды
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('students.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Студенты
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('sports.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Виды спорта
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="p-4 pb-[max(1rem,env(safe-area-inset-bottom))] border-t mt-auto">
                <div class="mb-3">
                    <p class="text-sm font-medium text-gray-800">{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</p>
                    <p class="text-xs text-gray-500">Преподаватель</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                        Выйти
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay для мобильных -->
        <div id="sidebarOverlay" class="hidden lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity"></div>

        <!-- Основной контент -->
        <main class="flex-1 overflow-y-auto h-dvh relative">
            <!-- Кнопка открытия меню (для мобильных) -->
            <button type="button" id="openSidebar" class="lg:hidden fixed top-4 left-4 z-30 p-2 bg-white rounded-lg shadow-md hover:bg-gray-50 transition">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <div class="p-4 sm:p-6">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Footer -->
    @hasSection('footer')
        @yield('footer')
    @endif

    <!-- Scripts -->
    @include('partials.tinymce-loader')
    @stack('scripts')
    <script>
        const sidebar = document.getElementById('sidebar');
        const closeBtn = document.getElementById('toggleSidebar');
        const openBtn = document.getElementById('openSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleMobileSidebar(open) {
            if (!sidebar) return;
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
            }
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar(false);
            });
        }

        if (openBtn) {
            openBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar(true);
            });
        }

        if (overlay) {
            overlay.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar(false);
            });
        }

        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024 && sidebar && openBtn) {
                if (!sidebar.contains(e.target) && !openBtn.contains(e.target)) {
                    toggleMobileSidebar(false);
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth < 1024) {
                sidebar?.classList.add('-translate-x-full');
            } else {
                sidebar?.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

