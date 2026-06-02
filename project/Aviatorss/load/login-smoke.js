/**
 * Smoke-тест входа в систему (форма логина + POST /login + dashboard).
 *
 * Запуск:
 *   .\load\k6.ps1 run -e K6_LOGIN=логин -e K6_PASSWORD=пароль load/login-smoke.js
 */
import http from 'k6/http';
import { check, sleep, fail } from 'k6';
import {
  assertCredentialsWork,
  getBaseUrl,
  login,
  smokeThresholds,
} from './lib/helpers.js';

export function setup() {
  const loginName = __ENV.K6_LOGIN;
  const password = __ENV.K6_PASSWORD;

  if (!loginName || !password) {
    fail('Задайте K6_LOGIN и K6_PASSWORD (учётная запись LDAP).');
  }

  if (!assertCredentialsWork(loginName, password)) {
    fail(
      `Вход не удался для K6_LOGIN="${loginName}" на ${getBaseUrl()}. Проверьте пароль и LDAP.`,
    );
  }

  return { login: loginName, password };
}

export const options = {
  vus: 3,
  duration: '30s',
  setupTimeout: '60s',
  thresholds: {
    ...smokeThresholds(),
    checks: ['rate>0.85'],
  },
};

export default function (data) {
  const jar = http.cookieJar();
  const base = getBaseUrl();

  if (!login(jar, data.login, data.password)) {
    sleep(1);
    return;
  }

  sleep(1);
}
