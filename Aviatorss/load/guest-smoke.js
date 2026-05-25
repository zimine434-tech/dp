/**
 * Smoke-тест публичных страниц (без авторизации).
 *
 * Запуск:
 *   k6 run load/guest-smoke.js
 *   k6 run -e BASE_URL=http://10.100.3.45:8888 load/guest-smoke.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { getBaseUrl, smokeThresholds } from './lib/helpers.js';

export const options = {
  vus: 5,
  duration: '30s',
  thresholds: smokeThresholds(),
};

const paths = [
  { path: '/', name: 'home' },
  { path: '/guest/news', name: 'guest news' },
  { path: '/guest/teams', name: 'guest teams' },
  { path: '/guest/competitions', name: 'guest competitions' },
  { path: '/guest/training-sessions', name: 'guest training sessions' },
];

export default function () {
  const base = getBaseUrl();

  for (const { path, name } of paths) {
    const res = http.get(`${base}${path}`, {
      tags: { name: `GET ${path}` },
    });

    check(res, {
      [`${name} status 200`]: (r) => r.status === 200,
    });

    sleep(0.5);
  }
}
