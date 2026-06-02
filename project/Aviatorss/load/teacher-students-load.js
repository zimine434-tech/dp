/**
 * Нагрузочный тест раздела «Студенты» под преподавателем (LDAP + сессия).
 *
 * Требуются учётные данные:
 *   K6_LOGIN, K6_PASSWORD
 *
 * Запуск:
 *   k6 run -e BASE_URL=http://127.0.0.1:8000 -e K6_LOGIN=teacher1 -e K6_PASSWORD=secret load/teacher-students-load.js
 */
import http from 'k6/http';
import { check, sleep, fail } from 'k6';
import { getBaseUrl, login, loadThresholds, envInt } from './lib/helpers.js';

const peakVus = envInt('K6_VUS', 10);

export function setup() {
  const loginName = __ENV.K6_LOGIN;
  const password = __ENV.K6_PASSWORD;

  if (!loginName || !password) {
    fail('Задайте K6_LOGIN и K6_PASSWORD (учётная запись преподавателя в LDAP).');
  }

  return { login: loginName, password };
}

export const options = {
  setupTimeout: '60s',
  stages: [
    { duration: '20s', target: Math.max(1, Math.floor(peakVus / 2)) },
    { duration: '1m', target: peakVus },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    ...loadThresholds(),
    checks: ['rate>0.9'],
  },
};

export default function (data) {
  const jar = http.cookieJar();
  const base = getBaseUrl();

  const loggedIn = login(jar, data.login, data.password);
  if (!loggedIn) {
    sleep(1);
    return;
  }

  const indexRes = http.get(`${base}/students`, {
    jar,
    tags: { name: 'GET /students' },
  });

  check(indexRes, {
    'students list 200': (r) => r.status === 200,
    'students page contains title': (r) => (r.body || '').includes('Студенты'),
  });

  sleep(Math.random() + 0.5);

  const dashboardRes = http.get(`${base}/dashboard`, {
    jar,
    tags: { name: 'GET /dashboard' },
  });

  check(dashboardRes, {
    'dashboard 200': (r) => r.status === 200,
  });

  sleep(Math.random() * 2 + 1);
}
