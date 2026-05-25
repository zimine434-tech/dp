/**
 * Нагрузочный тест входа в систему (LDAP).
 * Каждая итерация: GET /login → POST /login → GET /dashboard.
 *
 * Запуск:
 *   .\load\k6.ps1 run -e BASE_URL=http://127.0.0.1:8000 -e K6_LOGIN=логин -e K6_PASSWORD=пароль load/login-load.js
 *   .\load\k6.ps1 run -e K6_VUS=15 -e K6_LOGIN=... -e K6_PASSWORD=... load/login-load.js
 */
import http from 'k6/http';
import { check, sleep, fail } from 'k6';
import {
  assertCredentialsWork,
  getBaseUrl,
  login,
  logout,
  loadThresholds,
  envInt,
} from './lib/helpers.js';

const peakVus = envInt('K6_VUS', 10);

export function setup() {
  const loginName = __ENV.K6_LOGIN;
  const password = __ENV.K6_PASSWORD;

  if (!loginName || !password) {
    fail('Задайте K6_LOGIN и K6_PASSWORD (учётная запись LDAP).');
  }

  if (!assertCredentialsWork(loginName, password)) {
    fail(
      `Вход не удался для K6_LOGIN="${loginName}" на ${getBaseUrl()}. ` +
        'Проверьте пароль, LDAP и что приложение запущено (php artisan serve). ' +
        'При ошибке POST /login редирект идёт на /login, а не на /dashboard.',
    );
  }

  return { login: loginName, password };
}

export const options = {
  setupTimeout: '120s',
  stages: [
    { duration: '30s', target: Math.max(1, Math.floor(peakVus / 3)) },
    { duration: '1m', target: peakVus },
    { duration: '1m', target: peakVus },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    ...loadThresholds(),
    'http_req_duration{name:POST /login}': ['p(95)<8000'],
    checks: ['rate>0.8'],
  },
};

export default function (data) {
  const jar = http.cookieJar();
  const base = getBaseUrl();

  if (!login(jar, data.login, data.password)) {
    sleep(2);
    return;
  }

  logout(jar);

  sleep(Math.random() * 2 + 1);
}
