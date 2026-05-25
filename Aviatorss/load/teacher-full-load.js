/**
 * Смешанный сценарий преподавателя: dashboard, студенты, соревнования.
 *
 * Запуск:
 *   k6 run -e BASE_URL=... -e K6_LOGIN=... -e K6_PASSWORD=... load/teacher-full-load.js
 */
import http from 'k6/http';
import { check, sleep, fail } from 'k6';
import { getBaseUrl, login, loadThresholds, envInt } from './lib/helpers.js';

const peakVus = envInt('K6_VUS', 15);

export function setup() {
  const loginName = __ENV.K6_LOGIN;
  const password = __ENV.K6_PASSWORD;

  if (!loginName || !password) {
    fail('Задайте K6_LOGIN и K6_PASSWORD.');
  }

  return { login: loginName, password };
}

export const options = {
  setupTimeout: '60s',
  stages: [
    { duration: '30s', target: Math.max(1, Math.floor(peakVus / 3)) },
    { duration: '2m', target: peakVus },
    { duration: '30s', target: 0 },
  ],
  thresholds: loadThresholds(),
};

const teacherPaths = [
  '/dashboard',
  '/students',
  '/competitions?filter=all',
  '/teams',
  '/profile',
];

export default function (data) {
  const jar = http.cookieJar();
  const base = getBaseUrl();

  if (!login(jar, data.login, data.password)) {
    sleep(1);
    return;
  }

  const path = teacherPaths[Math.floor(Math.random() * teacherPaths.length)];
  const res = http.get(`${base}${path}`, {
    jar,
    tags: { name: `GET ${path.split('?')[0]}` },
  });

  check(res, {
    'authenticated page 200': (r) => r.status === 200,
    'not login redirect': (r) => !(r.url && r.url.includes('/login')),
  });

  sleep(Math.random() * 3 + 1);
}
