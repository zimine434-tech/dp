<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Главная страница') - Авиатор</title>
    
    <meta name="description" content="@yield('description', 'Панель управления для студентов')">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @stack('styles')
    @include('partials.form-interaction-fix')
    @yield('head')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Боковое меню -->
        <aside id="sidebar" class="bg-white shadow-lg flex flex-col h-dvh transition-all duration-300 ease-in-out w-64 fixed z-50 -translate-x-full lg:translate-x-0 lg:static">
            
            <div class="p-4 border-b flex items-center justify-center lg:justify-between relative min-h-[64px]">
                <h1 class="text-xl font-bold text-gray-800 sidebar-title">Главная страница</h1>
                <button type="button" id="toggleSidebar" class="p-2 rounded-lg hover:bg-gray-100 transition lg:hidden absolute right-4">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <button type="button" id="collapseSidebar" class="hidden lg:flex items-center justify-center p-2 rounded-lg hover:bg-gray-100 transition absolute top-1/2 right-2 -translate-y-1/2">
                    <svg id="collapseIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>
            
            <nav class="mt-4 flex-1 overflow-y-auto">
                <ul class="space-y-1 px-2">
                    <li>
                        <x-sidebar-nav-link :href="route('dashboard')" :routes="['dashboard']" label="Главная" :student="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                    <li>
                        <x-sidebar-nav-link
                            :href="route('profile')"
                            :routes="['profile', 'profile.*']"
                            label="Профиль"
                            :student="true"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                    <li>
                        <x-sidebar-nav-link
                            :href="route('competitions.index')"
                            :routes="['competitions.index', 'competitions.show', 'competitions.student.my', 'competitions.apply']"
                            label="Соревнования"
                            :student="true"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                    <li>
                        <x-sidebar-nav-link
                            :href="route('competitions.results')"
                            :routes="['competitions.results']"
                            label="Результаты соревнований"
                            active-tone="green"
                            :student="true"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                    <li>
                        <x-sidebar-nav-link
                            :href="route('training-sessions.index')"
                            :routes="['training-sessions.index', 'training-sessions.show', 'training-sessions.student.*', 'training-sessions.register', 'training-sessions.unregister']"
                            label="Тренировки"
                            :student="true"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                    <li>
                        <x-sidebar-nav-link :href="route('news.index')" :routes="['news.index', 'news.show']" label="Новости" :student="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                    <li>
                        <x-sidebar-nav-link :href="route('teams.index')" :routes="['teams.index', 'teams.show', 'teams.join-requests.*']" label="Команды" :student="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </x-sidebar-nav-link>
                    </li>
                </ul>
            </nav>
            
            <!-- Информация о пользователе и кнопка выхода -->
            <div class="p-4 pb-[max(1rem,env(safe-area-inset-bottom))] border-t mt-auto">
                <div class="mb-3 sidebar-user-info">
                    <p class="text-sm font-medium text-gray-800 sidebar-text">{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</p>
                    <p class="text-xs text-gray-500 sidebar-text">Студент</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 text-white px-2 py-2 rounded hover:bg-red-600 transition flex items-center justify-center sidebar-text" title="Выйти">
                        <svg class="w-5 h-5 sidebar-icon-only hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="sidebar-text">Выйти</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay для мобильных -->
        <div id="sidebarOverlay" class="hidden pointer-events-none lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity" aria-hidden="true"></div>

        <!-- Основной контент -->
        <main class="flex-1 overflow-y-auto h-dvh relative">
            <!-- Кнопка разворачивания меню (для мобильных) -->
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
        // Управление боковым меню
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const openBtn = document.getElementById('openSidebar');
        const collapseBtn = document.getElementById('collapseSidebar');
        const collapseIcon = document.getElementById('collapseIcon');
        const overlay = document.getElementById('sidebarOverlay');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarTitle = document.querySelector('.sidebar-title');
        const sidebarUserInfo = document.querySelector('.sidebar-user-info');
        const sidebarIconOnly = document.querySelector('.sidebar-icon-only');
        
        // Состояние меню (true = развернуто, false = свернуто)
        let isExpanded = true;
        
        // Загружаем состояние из localStorage
        const savedState = localStorage.getItem('sidebarExpanded');
        if (savedState !== null) {
            isExpanded = savedState === 'true';
            updateSidebar();
        }
        
        // Функция обновления состояния меню
        function updateSidebar() {
            const menuLinks = document.querySelectorAll('.sidebar-link');
            const sidebarIcons = document.querySelectorAll('.sidebar-icon');
            
            if (isExpanded) {
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64');
                sidebarTexts.forEach(el => {
                    el.classList.remove('hidden');
                    el.classList.add('block');
                });
                const headerDiv = sidebar.querySelector('.border-b');
                if (headerDiv) {
                    headerDiv.classList.remove('justify-center');
                    headerDiv.classList.add('justify-between');
                }
                if (sidebarTitle) sidebarTitle.classList.remove('hidden');
                if (sidebarUserInfo) sidebarUserInfo.classList.remove('hidden');
                if (sidebarIconOnly) sidebarIconOnly.classList.add('hidden');
                menuLinks.forEach(link => {
                    link.classList.remove('justify-center', 'px-2');
                    link.classList.add('justify-start', 'px-4');
                });
                sidebarIcons.forEach(icon => {
                    icon.classList.remove('mx-auto');
                    icon.classList.add('mr-0');
                });
                if (collapseIcon) {
                    collapseIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>';
                }
            } else {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-16');
                sidebarTexts.forEach(el => {
                    el.classList.add('hidden');
                    el.classList.remove('block');
                });
                const headerDiv = sidebar.querySelector('.border-b');
                if (headerDiv) {
                    headerDiv.classList.remove('justify-between');
                    headerDiv.classList.add('justify-center');
                }
                if (sidebarTitle) sidebarTitle.classList.add('hidden');
                if (sidebarUserInfo) sidebarUserInfo.classList.add('hidden');
                if (sidebarIconOnly) sidebarIconOnly.classList.remove('hidden');
                menuLinks.forEach(link => {
                    link.classList.remove('justify-start', 'px-4');
                    link.classList.add('justify-center', 'px-2');
                });
                sidebarIcons.forEach(icon => {
                    icon.classList.remove('mr-0');
                    icon.classList.add('mx-auto');
                });
                if (collapseIcon) {
                    collapseIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>';
                }
            }
            localStorage.setItem('sidebarExpanded', isExpanded);
        }
        
        // Кнопка сворачивания/разворачивания (для десктопа)
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                isExpanded = !isExpanded;
                updateSidebar();
            });
        }
        
        // Функция открытия/закрытия меню на мобильных
        function toggleMobileSidebar(open) {
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('hidden', 'pointer-events-none');
                    overlay.setAttribute('aria-hidden', 'false');
                }
            } else {
                sidebar.classList.add('-translate-x-full');
                if (overlay) {
                    overlay.classList.add('hidden', 'pointer-events-none');
                    overlay.setAttribute('aria-hidden', 'true');
                }
            }
        }
        
        // Кнопка закрытия (для мобильных)
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar(false);
            });
        }
        
        // Кнопка открытия (для мобильных)
        if (openBtn) {
            openBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar(true);
            });
        }
        
        // Закрытие меню при клике на overlay
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar(false);
            });
        }
        
        // Закрытие меню при клике вне его на мобильных
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) && !openBtn.contains(e.target) && (!overlay || !overlay.contains(e.target))) {
                    toggleMobileSidebar(false);
                }
            }
        });
        
        // Адаптивность для мобильных/десктопа
        window.addEventListener('resize', () => {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
            } else {
                sidebar.classList.remove('-translate-x-full');
                if (overlay) {
                    overlay.classList.add('hidden', 'pointer-events-none');
                    overlay.setAttribute('aria-hidden', 'true');
                }
            }
        });
    </script>
</body>
</html>

