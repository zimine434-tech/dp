<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-4xl font-bold mb-8">Добро пожаловать</h1>
            <a href="{{ route('login') }}" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-200">
                Войти
            </a>
        </div>
    </div>
</body>
</html>