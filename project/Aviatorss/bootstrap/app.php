<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Uri;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            if ($request->is('logout')) {
                return redirect()->route('logout');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Сессия истекла. Обновите страницу и повторите действие.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return redirect()
                ->route('login')
                ->with('status', 'Сессия истекла. Войдите снова.');
        });

        $exceptions->renderable(function (PostTooLargeException $e, Request $request) {
            $message = 'Превышен размер файла.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
            }

            $referer = $request->headers->get('Referer');
            if ($referer) {
                $refHost = parse_url($referer, PHP_URL_HOST);
                if ($refHost && strcasecmp((string) $refHost, $request->getHost()) === 0) {
                    return redirect()->to(
                        (string) Uri::of($referer)->withQuery(['upload_err' => '1'])
                    );
                }
            }

            $path = trim($request->path(), '/');
            if (preg_match('#^competitions/([^/]+)/photos$#', $path, $m)) {
                return redirect()->route('competitions.photos', ['competition' => $m[1], 'upload_err' => '1']);
            }
            if ($path === 'home-carousel-photos') {
                return redirect()->route('home-carousel-photos.index', ['upload_err' => '1']);
            }
            if ($path === 'profile/avatar') {
                return redirect()->route('profile.avatar.edit', ['upload_err' => '1']);
            }
            if ($path === 'news') {
                return redirect()->route('news.create', ['upload_err' => '1']);
            }
            if (preg_match('#^news/([^/]+)$#', $path, $m) && $m[1] !== 'create') {
                return redirect()->route('news.edit', ['news' => $m[1], 'upload_err' => '1']);
            }

            $previous = url()->previous();
            if ($previous) {
                return redirect()->to(
                    (string) Uri::of($previous)->withQuery(['upload_err' => '1'])
                );
            }

            return redirect()->route('competitions.index', ['upload_err' => '1']);
        });
    })->create();
