/**
 * Стресс-тест входа: максимальная нагрузка (по умолчанию 50 VU).
 *
 * Запуск:
 *   .\load\k6.ps1 run load/login-stress-max.js
 *   .\load\k6.ps1 run -e K6_VUS=100 load/login-stress-max.js
 */
import http from 'k6/http';
import { sleep, fail } from 'k6';
import {
  assertCredentialsWork,
  getBaseUrl,
  login,
  logout,
  envInt,
} from './lib/helpers.js';

const peakVus = envInt('K6_VUS', 50);

export function setup() {
  const loginName = __ENV.K6_LOGIN;
  const password = __ENV.K6_PASSWORD;

  if (!loginName || !password) {
    fail('Задайте K6_LOGIN и K6_PASSWORD (учётная запись LDAP).');
  }

  if (!assertCredentialsWork(loginName, password)) {
    fail(
      `Вход не удался для K6_LOGIN="${loginName}" на ${getBaseUrl()}. ` +
        'Проверьте пароль, LDAP и php artisan serve.',
    );
  }

  return { login: loginName, password, peakVus };
}

export const options = {
  setupTimeout: '180s',
  stages: [
    { duration: '1m', target: Math.max(5, Math.floor(peakVus / 4)) },
    { duration: '2m', target: peakVus },
    { duration: '2m', target: peakVus },
    { duration: '1m', target: 0 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.15'],
    http_req_duration: ['p(95)<15000'],
    'http_req_duration{name:POST /login}': ['p(95)<20000'],
    checks: ['rate>0.7'],
  },
};

export default function (data) {
  const jar = http.cookieJar();

  if (!login(jar, data.login, data.password)) {
    sleep(2);
    return;
  }

  logout(jar);
  sleep(Math.random() * 1.5 + 0.5);
}

export function handleSummary(data) {
  const peak = data.metrics.vus_max?.values?.max ?? peakVus;
  const checksRate = data.metrics.checks?.values?.rate ?? 0;
  const p95 = data.metrics.http_req_duration?.values?.['p(95)'] ?? 0;
  const failed = data.metrics.http_req_failed?.values?.rate ?? 0;
  const iters = data.metrics.iterations?.values?.count ?? 0;

  console.log('\n=== STRESS MAX SUMMARY ===');
  console.log(`Peak VUs (target): ${peakVus}`);
  console.log(`Peak VUs (actual):  ${peak}`);
  console.log(`Checks success:     ${(checksRate * 100).toFixed(2)}%`);
  console.log(`HTTP failed:        ${(failed * 100).toFixed(2)}%`);
  console.log(`p95 duration:       ${p95.toFixed(2)} ms`);
  console.log(`Iterations:         ${iters}`);
  console.log('==========================\n');

  return {};
}
