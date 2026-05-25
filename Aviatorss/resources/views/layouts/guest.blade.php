<!DOCTYPE html>
<html lang="ru" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title>@yield('title', 'Главная') - Авиатор</title>
    
    <meta name="description" content="@yield('description', 'Спортивный клуб Авиатор')">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @stack('styles')
    @yield('head')
</head>
<body class="m-0 bg-gray-100 flex flex-col min-h-screen overflow-x-hidden">
    <!-- Навигация -->
    <nav class="bg-white shadow-md">
        <div class="mx-auto max-w-[min(120rem,calc(100vw-2rem))] px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition">
                        Авиатор
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        Главная
                    </a>
                    <a href="{{ route('guest.news') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        Новости
                    </a>
                    <a href="{{ route('guest.sports') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        Виды спорта
                    </a>
                    <a href="{{ route('guest.teams') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        Команды
                    </a>
                    <a href="{{ route('guest.results') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        Результаты соревнований
                    </a>
                    <a href="{{ route('login') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200 text-sm font-medium">
                        Войти
                    </a>
                </div>
                
                <!-- Мобильное меню -->
                <div class="md:hidden">
                    <button id="mobileMenuButton" class="text-gray-700 hover:text-blue-600 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Мобильное меню (скрыто по умолчанию) -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md text-base font-medium">
                    Главная
                </a>
                <a href="{{ route('guest.news') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md text-base font-medium">
                    Новости
                </a>
                <a href="{{ route('guest.sports') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md text-base font-medium">
                    Виды спорта
                </a>
                <a href="{{ route('guest.teams') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md text-base font-medium">
                    Команды
                </a>
                <a href="{{ route('guest.results') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md text-base font-medium">
                    Результаты соревнований
                </a>
                <a href="{{ route('login') }}" class="block px-3 py-2 bg-blue-500 text-white rounded-md text-base font-medium hover:bg-blue-600">
                    Войти
                </a>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="{{ request()->routeIs('home') ? 'flex-1 w-full pt-0 pb-8' : 'flex-1 mx-auto w-full max-w-[min(120rem,calc(100vw-2rem))] px-4 py-8 sm:px-6 lg:px-8' }}">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-auto">
        <div class="mx-auto max-w-[min(120rem,calc(100vw-2rem))] px-4 py-6 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; {{ date('Y') }} Авиатор. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    @stack('scripts')
    @include('guest.partials.mobile-photo-pinch')
    
    <script>
        // Управление мобильным меню
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>

