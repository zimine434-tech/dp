import 'dotenv/config';
import http from 'node:http';
import { Bot, Keyboard } from '@maxhub/max-bot-api';

const token = process.env.BOT_TOKEN;
if (!token) {
  console.error('Укажите BOT_TOKEN в переменной окружения или в файле .env (см. .env.example).');
  process.exit(1);
}

const laravelUrl = process.env.LARAVEL_APP_URL?.replace(/\/$/, '');
const botApiKey = process.env.BOT_API_SECRET;
const notifySecret = process.env.BOT_NOTIFY_SECRET;
const notifyPort = Number(process.env.NOTIFY_SERVER_PORT || 3999);

const bot = new Bot(token);

async function laravelFetch(path, { method = 'GET', body } = {}) {
  if (!laravelUrl || !botApiKey) {
    throw new Error('Не заданы LARAVEL_APP_URL или BOT_API_SECRET в .env');
  }
  const bodyText = body != null ? JSON.stringify(body) : undefined;
  const res = await fetch(`${laravelUrl}/api${path}`, {
    method,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Bot-Api-Key': botApiKey,
    },
    body: bodyText,
  });

  const text = await res.text();
  let json;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = null;
  }
  if (!res.ok) {
    const hint = typeof json?.message === 'string' ? json.message : text?.slice?.(0, 200);
    if (res.status === 422) {
      console.error('Laravel 422. Request body was:', bodyText);
      console.error('Laravel 422. Response was:', text?.slice?.(0, 500));
    }
    throw new Error(`Laravel HTTP ${res.status}: ${hint || 'ошибка'}`);
  }
  return json;
}

let cachedSports = null;
let cachedSportsAt = 0;
const SPORTS_CACHE_MS = 120_000;

async function fetchSports(force = false) {
  const now = Date.now();
  if (!force && cachedSports && now - cachedSportsAt < SPORTS_CACHE_MS) {
    return cachedSports;
  }
  const payload = await laravelFetch('/bot/sports');
  const rows = Array.isArray(payload?.data) ? payload.data : [];
  cachedSports = rows.map((r) => ({ id: Number(r.id), name: String(r.name ?? '') }));
  cachedSportsAt = now;
  return cachedSports;
}

const SCOPES = /** @type {const} */ ({
  training: { label: 'Тренировки', noun: 'тренировкам' },
  competition: { label: 'Соревнования', noun: 'соревнованиям' },
});

function normalizeScope(scope) {
  return scope === 'competition' ? 'competition' : 'training';
}

async function fetchSubscription(maxUserId, scope) {
  const s = normalizeScope(scope);
  const payload = await laravelFetch(`/bot/subscriptions/${maxUserId}?scope=${encodeURIComponent(s)}`);
  const ids = payload?.data?.sport_ids ?? [];
  return Array.isArray(ids) ? ids.map((n) => Number(n)) : [];
}

async function saveSubscription(maxUserId, chatId, scope, sportIds) {
  const s = normalizeScope(scope);
  await laravelFetch('/bot/subscriptions', {
    method: 'PUT',
    body: {
      max_user_id: maxUserId,
      chat_id: chatId ?? null,
      scope: s,
      sport_ids: [...new Set(sportIds)].filter((id) => Number.isFinite(id) && id > 0),
    },
  });
}

function chatIdFromCtx(ctx) {
  if (typeof ctx.chatId === 'number') return ctx.chatId;
  return ctx.message?.recipient?.chat_id ?? null;
}

function maxUserFromCtx(ctx) {
  const u = ctx.user ?? ctx.callback?.user ?? ctx.message?.sender;
  return typeof u?.user_id === 'number' ? u.user_id : undefined;
}

function trimBtnLabel(label) {
  if (label.length <= 48) return label;
  return `${label.slice(0, 45)}…`;
}

function buildSportKeyboardRows(scope, sports, selectedIds) {
  const scopeKey = normalizeScope(scope);
  const sel = new Set(selectedIds);
  const rows = sports.map((sport) => [
    Keyboard.button.callback(
      trimBtnLabel(`${sel.has(sport.id) ? '✓ ' : ''}${sport.name}`),
      `sub:${scopeKey}:${sport.id}`,
    ),
  ]);
  rows.push([
    Keyboard.button.callback('Выбрать всё', `sub:${scopeKey}:all`),
    Keyboard.button.callback('Очистить', `sub:${scopeKey}:clear`),
  ]);
  rows.push([Keyboard.button.callback('← Назад', 'menu:main')]);
  return rows;
}

function selectionBannerText(scope, namesById, selectedIds, sportsLen) {
  const s = normalizeScope(scope);
  if (selectedIds.length === 0) {
    return `Сейчас уведомления по ${SCOPES[s].noun} выключены (нет выбранных видов спорта). Выберите виды ниже.`;
  }
  const lines = selectedIds
    .map((id) => namesById.get(id))
    .filter(Boolean)
    .map((name) => `• ${name}`);
  const extra = lines.length !== selectedIds.length ? `\n(всего id: ${selectedIds.length})` : '';
  return `Вы подписаны на уведомления по ${SCOPES[s].noun} для ${selectedIds.length} из ${sportsLen} видов спорта:\n${lines.join('\n')}${extra}`;
}

async function replyMainMenu(ctx) {
  await ctx.reply('Выберите раздел уведомлений:', {
    attachments: [
      Keyboard.inlineKeyboard([
        [Keyboard.button.callback('Тренировки', 'menu:training')],
        [Keyboard.button.callback('Соревнования', 'menu:competition')],
      ]),
    ],
  });
}

async function replyScopeMenu(ctx, scope, { forceSports = false } = {}) {
  const s = normalizeScope(scope);
  const maxUid = maxUserFromCtx(ctx);
  if (maxUid == null) {
    await ctx.reply('Не удалось определить ваш профиль в MAX.');
    return;
  }
  const chatId = chatIdFromCtx(ctx);

  let sports;
  try {
    sports = await fetchSports(forceSports);
  } catch (err) {
    await ctx.reply(
      `Не получилось загрузить виды спорта из сайта приложения (${err?.message ?? err}). Проверьте LARAVEL_APP_URL, BOT_API_SECRET и что команда Laravel запущена.`,
    );
    return;
  }

  if (!sports.length) {
    await ctx.reply('В базе приложения пока нет ни одного вида спорта — добавьте их через администрирование Aviatorss.');
    return;
  }

  let selected;
  try {
    selected = await fetchSubscription(maxUid, s);
  } catch {
    selected = [];
  }

  const namesById = new Map(sports.map((s) => [s.id, s.name]));
  const banner = `Раздел: ${SCOPES[s].label}\n\n${selectionBannerText(s, namesById, selected, sports.length)}\n\nНажимайте на вид спорта, чтобы вкл./выкл. уведомления.`;

  await ctx.reply(banner, {
    attachments: [Keyboard.inlineKeyboard(buildSportKeyboardRows(s, sports, selected))],
  });

  await saveSubscription(maxUid, chatId, s, selected).catch(() => {});
}

async function persistAndRefreshSportMessage(ctx, scope, nextSelected) {
  const s = normalizeScope(scope);
  const maxUid = maxUserFromCtx(ctx);
  if (maxUid == null) {
    await ctx.answerOnCallback({ notification: 'Не удалось определить пользователя MAX.' });
    return;
  }

  let sports;
  try {
    sports = await fetchSports();
  } catch (err) {
    await ctx.answerOnCallback({
      notification: `Ошибка: ${String(err?.message ?? err)}`.slice(0, 180),
    });
    return;
  }

  await saveSubscription(maxUid, chatIdFromCtx(ctx), s, nextSelected).catch(async (err) => {
    await ctx.answerOnCallback({
      notification: `Не сохранилось: ${String(err?.message ?? err)}`.slice(0, 180),
    });
    throw err;
  });

  const namesById = new Map(sports.map((s) => [s.id, s.name]));
  const text = `Раздел: ${SCOPES[s].label}\n\n${selectionBannerText(s, namesById, nextSelected, sports.length)}\n\nНажимайте на вид спорта, чтобы вкл./выкл. уведомления.`;

  if (!ctx.messageId || !ctx.message?.body) {
    await ctx.answerOnCallback({ notification: 'Сохранено' });
    return;
  }

  await ctx.answerOnCallback({ notification: 'Сохранено' });
  await ctx.editMessage({
    text,
    attachments: [Keyboard.inlineKeyboard(buildSportKeyboardRows(s, sports, nextSelected))],
  }).catch(async () => {
    await ctx.reply('Настройки обновились — откройте /menu заново.', {
      attachments: [Keyboard.inlineKeyboard(buildSportKeyboardRows(s, sports, nextSelected))],
    });
  });
}

bot.catch((err) => {
  console.error(err);
});

bot.command('start', async (ctx) => {
  await ctx.reply(
    [
      'Привет! Я бот уведомлений приложения.',
      '',
      '/menu — настройки уведомлений (тренировки/соревнования).',
      '/sports — то же самое, что /menu.',
      '/help — краткая справка.',
    ].join('\n'),
  );
  await replyMainMenu(ctx).catch(console.error);
});

bot.command('help', async (ctx) => {
  await ctx.reply(
    [
      'Команды:',
      '/start — регистрация и выбор спорта',
      '/menu — выбрать раздел и виды спорта для уведомлений',
      '/sports — псевдоним /menu',
      '',
      'Уведомления приходят только по тем видам спорта, которые вы отметите в нужном разделе.',
    ].join('\n'),
  );
});

bot.command(['menu', 'sports'], async (ctx) => {
  await replyMainMenu(ctx).catch(console.error);
});

bot.on('bot_started', async (ctx) => {
  await replyMainMenu(ctx).catch(console.error);
});

bot.action(/^menu:(training|competition|main)$/, async (ctx) => {
  const target = ctx.match?.[1];
  if (target === 'training' || target === 'competition') {
    await ctx.answerOnCallback({ notification: SCOPES[target].label });
    await replyScopeMenu(ctx, target, { forceSports: true }).catch(console.error);
    return;
  }
  await ctx.answerOnCallback({ notification: 'Меню' });
  await replyMainMenu(ctx).catch(console.error);
});

bot.action(/^sub:(training|competition):all$/, async (ctx) => {
  const scope = ctx.match?.[1];
  let sports;
  try {
    sports = await fetchSports(true);
  } catch (err) {
    await ctx.answerOnCallback({
      notification: `Ошибка: ${String(err?.message ?? err)}`.slice(0, 180),
    });
    return;
  }

  await persistAndRefreshSportMessage(ctx, scope, sports.map((s) => s.id)).catch(console.error);
});

bot.action(/^sub:(training|competition):clear$/, async (ctx) => {
  const scope = ctx.match?.[1];
  await persistAndRefreshSportMessage(ctx, scope, []).catch(console.error);
});

bot.action(/^sub:(training|competition):(\d+)$/, async (ctx) => {
  const scope = ctx.match?.[1];
  const id = Number(ctx.match?.[2]);
  if (!Number.isFinite(id)) return;

  const maxUid = maxUserFromCtx(ctx);
  if (maxUid == null) return;

  let selected = await fetchSubscription(maxUid, scope).catch(() => []);

  const setIds = new Set(selected);
  if (setIds.has(id)) setIds.delete(id);
  else setIds.add(id);

  await persistAndRefreshSportMessage(ctx, scope, [...setIds]).catch(console.error);
});

function startNotifyHttpServer() {
  if (!notifySecret) {
    console.warn('BOT_NOTIFY_SECRET не задан — Laravel не сможет отправлять уведомления на этот процесс.');
    return;
  }

  const server = http.createServer(async (req, res) => {
      if (req.method !== 'POST' || req.url !== '/internal/notify') {
        res.writeHead(404).end();
        return;
      }

      let raw = '';
      for await (const chunk of req) {
        raw += chunk;
      }

      if ((req.headers['x-bot-notify-secret'] ?? '').trim() !== notifySecret) {
        console.warn('Notify rejected: bad X-Bot-Notify-Secret');
        res.writeHead(401).end();
        return;
      }

      let json;
      try {
        json = raw ? JSON.parse(raw) : {};
      } catch {
        console.warn('Notify rejected: invalid JSON');
        res.writeHead(400).end();
        return;
      }

      const list = Array.isArray(json.recipients) ? json.recipients : [];
      console.log(`Notify received: recipients=${list.length}`);
      for (const item of list) {
        const uid = item?.max_user_id;
        const chatId = item?.chat_id;
        const text = item?.text;
        if (typeof text !== 'string' || !text.trim() || typeof uid !== 'number') {
          continue;
        }
        try {
          if (typeof chatId === 'number' && Number.isFinite(chatId)) {
            await bot.api.sendMessageToChat(chatId, text);
          } else {
            await bot.api.sendMessageToUser(uid, text);
          }
        } catch (e) {
          console.error('notify send failed', { uid, chatId }, e?.message ?? e);
        }
      }

      res.writeHead(204).end();
    });

  server.on('error', (err) => {
    if (err?.code === 'EADDRINUSE') {
      console.error(`Порт ${notifyPort} уже занят. Останови другой запущенный бот или поменяй NOTIFY_SERVER_PORT.`);
      return;
    }
    console.error('Notify HTTP server error', err);
  });

  server.listen(notifyPort, () => {
    console.log(`Приём уведомлений Laravel: POST http://127.0.0.1:${notifyPort}/internal/notify`);
  });
}

startNotifyHttpServer();
await bot.start();
console.log('Бот MAX запущен (long polling)...');