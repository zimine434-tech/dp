# Дипломный проект
Дипломный проект (ДП) студента специальности 09.02.07 Информационные системы и программирование, квалификация **_«Специалист по информационным системам»_**

Тема: **_Веб-приложение спортивного клуба «Авиатор»_**

Студент, группа: **_Зимин Георгий Муйдинжонович, ИС-22-2_**

## Структура
Обязательная структура каталогов:

```
├── docs
│   ├── Задание на курсовое проектирование.pdf (сканированный документ с подписями)
│   ├── Пояснительная записка.docx
│   ├── Пояснительная записка.pdf
│   ├── Презентация.pdf
│   └── Презентация.pptx
├── project
│    └── Исходные файлы проекта
├── README.md
└── .gitignore
```

## Установка

1. Клонировать репозиторий:
  ```console
  git clone https://gitlab.irkat.ru/22101/k-pr.git
  ```

2. Переходим в папку проекта:
  ```console
  cd project
  cd Aviatorss
  ```

3. Установить зависимости PHP:
  ```console
  composer install
  ```

4. Настроить окружение:

  Скопировать файл .env.example в .env:
  ```console
  cp .env.example .env
  ```

  Сгенерировать ключ приложения:
  ```console
  php artisan key:generate
  ```

  Настроить подключение к базе данных в файле .env:
  ```console
  DB_CONNECTION=mysql
  DB_HOST = 127.0.0.1
  DB_PORT = 3306
  DB_DATABASE=score_system
  DB_USERNAME=root
  DB_PASSWORD=
  ```

5. Создать и заполнить базу данных:
  ```console
  php artisan migrate
  php artisan db:seed
  ```

## Запуск

1. Запустить приложение:
  ```console
  php artisan serve
  ```

2. Перейдите в браузере по адресу: http://localhost:8000

## Устранение неполадок

Если возникнут проблемы:

1. Проверьте версии PHP и MySQL
2. Убедитесь, что все расширения PHP установлены
3. Проверьте права доступа к папкам storage и bootstrap/cache
4. Очистите кэш:
  ```console
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  ```
