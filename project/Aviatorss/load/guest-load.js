/**
 * Нагрузочный тест публичных страниц (ступенчатый разгон).
 *
 * Запуск:
 *   k6 run load/guest-load.js
 *   k6 run -e BASE_URL=http://127.0.0.1:8000 load/guest-load.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { getBaseUrl, loadThresholds, envInt } from './lib/helpers.js';

const peakVus = envInt('K6_VUS', 30);

export const options = {
  stages: [
    { duration: '30s', target: Math.max(1, Math.floor(peakVus / 3)) },
    { duration: '1m', target: peakVus },
    { duration: '1m', target: peakVus },
    { duration: '30s', target: 0 },
  ],
  thresholds: loadThresholds(),
};

const paths = ['/guest/news', '/guest/teams', '/guest/competitions'];

export default function () {
  const base = getBaseUrl();
  const path = paths[Math.floor(Math.random() * paths.length)];

  const res = http.get(`${base}${path}`, {
    tags: { name: `GET ${path}` },
  });

  check(res, {
    'status is 200': (r) => r.status === 200,
    'response has body': (r) => (r.body || '').length > 0,
  });

  sleep(Math.random() * 2 + 1);
}
