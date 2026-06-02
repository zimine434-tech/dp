<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureAllowedRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowedRoles = $roles ?: ['teacher', 'student'];

        if (! $user) {
            return $this->deny($request);
        }

        if (! in_array($user->role, $allowedRoles, true)) {
            Log::warning('Попытка доступа с недопустимой ролью', [
                'login' => $user->login,
                'role' => $user->role,
                'allowed' => $allowedRoles,
                'path' => $request->path(),
            ]);

            return $this->deny($request);
        }

        return $next($request);
    }

    private function deny(Request $request): Response
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        abort(403, 'Вход воспрещён');
    }
}

