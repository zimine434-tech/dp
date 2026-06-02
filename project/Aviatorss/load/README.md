# Нагрузочное тестирование (Grafana k6)

Скрипты проверяют, как приложение **Aviatorss** ведёт себя под одновременной нагрузкой многих пользователей.

## Установка k6

- Windows (Chocolatey): `choco install k6`
- Или: https://grafana.com/docs/k6/latest/set-up/install-k6/

Проверка: `k6 version`

### Если в терминале «k6 не распознано»

В Cursor PATH часто не обновляется после winget. Используйте обёртку из проекта:

```powershell
cd Aviatorss
.\load\k6.ps1 version
.\load\k6.ps1 run load/guest-smoke.js
```

Или полный путь: `& "C:\Program Files\k6\k6.exe" version`

## Перед запуском

1. Запустите приложение (локально или на staging):

   ```powershell
   cd Aviatorss
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. Укажите URL:

   ```powershell
   $env:BASE_URL = "http://127.0.0.1:8000"
   ```

3. Для сценариев с авторизацией нужны LDAP-логин и пароль.

   **Через файл (рекомендуется):**

   ```powershell
   Copy-Item load\env.example load\.env
   notepad load\.env
   ```

   Заполните `K6_LOGIN` и `K6_PASSWORD` в **корневом** `.env` проекта или в `load/.env` — `.\load\k6.ps1` подхватит их сам.

   **Или в PowerShell:**

   ```powershell
   $env:K6_LOGIN = "ваш_логин"
   $env:K6_PASSWORD = "ваш_пароль"
   ```

## Скрипты

| Файл | Назначение | Авторизация |
|------|------------|-------------|
| `guest-smoke.js` | Быстрая проверка публичных страниц (5 VU, 30 с) | Нет |
| `guest-load.js` | Ступенчатый разгон по `/guest/*` | Нет |
| `login-smoke.js` | Вход: форма → POST /login → dashboard (3 VU) | LDAP (K6_LOGIN) |
| `login-load.js` | Нагрузка на вход + выход (ступени, 10 VU) | LDAP (K6_LOGIN) |
| `login-stress-max.js` | Стресс входа (по умолчанию **50 VU**, `K6_VUS` для большего) | LDAP (K6_LOGIN) |
| `teacher-students-load.js` | Список студентов + dashboard | LDAP (K6_LOGIN) |
| `teacher-full-load.js` | Смесь страниц преподавателя | LDAP (K6_LOGIN) |

## Примеры запуска

Из корня проекта `Aviatorss`:

```powershell
# Публичные страницы (без пароля)
.\load\k6.ps1 run load/guest-smoke.js
.\load\k6.ps1 run -e BASE_URL=http://10.100.3.45:8888 load/guest-load.js

# Вход в систему (LDAP)
.\load\k6.ps1 run -e BASE_URL=http://127.0.0.1:8000 -e K6_LOGIN=логин -e K6_PASSWORD=пароль load/login-smoke.js
.\load\k6.ps1 run -e K6_VUS=10 -e K6_LOGIN=логин -e K6_PASSWORD=пароль load/login-load.js

# Преподаватель (нужен LDAP)
.\load\k6.ps1 run -e BASE_URL=http://127.0.0.1:8000 -e K6_LOGIN=teacher -e K6_PASSWORD=*** load/teacher-students-load.js

# Больше виртуальных пользователей
.\load\k6.ps1 run -e K6_VUS=25 load/guest-load.js
```

## Что смотреть в отчёте

- **http_req_duration (p95)** — время ответа у 95% запросов
- **http_req_failed** — доля ошибок (4xx/5xx)
- **http_reqs** — сколько запросов в секунду выдержал сервер
- **checks** — доля успешных проверок в скрипте

Пороги (thresholds) заданы в скриптах; при превышении k6 завершится с кодом ≠ 0.

## Grafana Cloud (опционально)

```powershell
k6 cloud login
k6 cloud run load/guest-smoke.js
```

## Ошибка «redirect to dashboard» 0%

`POST /login` отвечает **302**, но ведёт на **`/login`**, а не на **`/dashboard`** — LDAP не принял логин/пароль (или учётка заблокирована / не из разрешённой группы).

Проверьте:

```powershell
$env:BASE_URL = "http://127.0.0.1:8000"   # тот же URL, что в браузере
$env:K6_LOGIN = "ваш_логин"
$env:K6_PASSWORD = "ваш_пароль"
.\load\k6.ps1 run load/login-smoke.js
```

Войдите теми же данными в браузере на этом же `BASE_URL`. После правки скрипт в `setup` остановится сразу с понятной ошибкой, если вход не работает.

## Важно

- Не гоняйте большую нагрузку на **учебную БД** (`22101_Unit`) одновременно с PHPUnit.
- Массовый `POST /login` нагружает **LDAP** — для тяжёлых тестов предпочтительнее сценарии `guest-*.js` или отдельная тестовая учётка.
- Функциональные тесты (`php artisan test`) и k6 дополняют друг друга, не заменяют.

## Структура

```
load/
  lib/helpers.js          — CSRF, login, BASE_URL, thresholds
  guest-smoke.js
  guest-load.js
  login-smoke.js
  login-load.js
  teacher-students-load.js
  teacher-full-load.js
  env.example
  README.md
```
