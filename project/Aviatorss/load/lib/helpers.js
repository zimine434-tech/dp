import http from 'k6/http';
import { check } from 'k6';

/**
 * Базовый URL приложения (без завершающего слэша).
 */
export function getBaseUrl() {
  return (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
}

/**
 * Заголовок Location после редиректа (Laravel / k6).
 */
export function getRedirectLocation(res) {
  const loc = res.headers.Location || res.headers.location || '';
  return Array.isArray(loc) ? (loc[0] || '') : loc;
}

/**
 * Извлекает CSRF-токен Laravel из HTML страницы логина.
 */
export function extractCsrfToken(html) {
  const body = typeof html === 'string' ? html : html?.body ?? '';
  const match = body.match(/name="_token"\s+value="([^"]+)"/);
  return match ? match[1] : null;
}

/**
 * Вход через POST /login с сохранением сессии в cookie jar.
 * @returns {boolean} true, если редирект после успешного входа
 */
export function login(jar, loginName, password) {
  const base = getBaseUrl();
  const loginPage = http.get(`${base}/login`, { jar, tags: { name: 'GET /login' } });

  const csrfOk = check(loginPage, {
    'login page is 200': (r) => r.status === 200,
    'csrf token present': (r) => extractCsrfToken(r.body) !== null,
  });

  if (!csrfOk) {
    return false;
  }

  const token = extractCsrfToken(loginPage.body);
  const payload = {
    login: loginName,
    password: password,
    _token: token,
  };

  const res = http.post(`${base}/login`, payload, {
    jar,
    redirects: 0,
    tags: { name: 'POST /login' },
  });

  const location = getRedirectLocation(res);
  const toDashboard = location.includes('dashboard');
  const backToLogin = /\/login\/?(\?|$)/.test(location) || location.endsWith('/login');

  const ok = check(res, {
    'login redirects (302)': (r) => r.status === 302,
    'redirect to dashboard': () => toDashboard,
    'not rejected to login page': () => !backToLogin,
  });

  if (!ok || !toDashboard) {
    return false;
  }

  const dashboard = http.get(`${base}/dashboard`, {
    jar,
    tags: { name: 'GET /dashboard' },
  });

  return check(dashboard, {
    'dashboard after login is 200': (r) => r.status === 200,
  });
}

/**
 * Проверка учётных данных до нагрузочного прогона.
 */
export function assertCredentialsWork(loginName, password) {
  const jar = http.cookieJar();
  if (!login(jar, loginName, password)) {
    return false;
  }
  logout(jar);
  return true;
}

/**
 * Выход из сессии (нужен для повторного входа в той же VU).
 */
export function logout(jar) {
  const base = getBaseUrl();
  const page = http.get(`${base}/dashboard`, {
    jar,
    tags: { name: 'GET /dashboard (logout)' },
  });

  const token = extractCsrfToken(page.body);
  if (!token) {
    return false;
  }

  const res = http.post(`${base}/logout`, { _token: token }, {
    jar,
    redirects: 0,
    tags: { name: 'POST /logout' },
  });

  return res.status === 302 || res.status === 303;
}

export function smokeThresholds() {
  return {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<3000'],
  };
}

export function loadThresholds() {
  return {
    http_req_failed: ['rate<0.1'],
    http_req_duration: ['p(95)<5000'],
  };
}

export function envInt(name, fallback) {
  const value = __ENV[name];
  if (value === undefined || value === '') {
    return fallback;
  }
  return parseInt(value, 10);
}
