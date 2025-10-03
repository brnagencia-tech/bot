<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            throw new AccessDeniedHttpException('Usuário não autenticado.');
        }

        $allowed = collect($roles)->contains(function ($role) use ($user) {
            $currentRole = $user->role instanceof UserRole ? $user->role->value : $user->role;
            return $currentRole === $role;
        });

        if (! $allowed) {
            throw new AccessDeniedHttpException('Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
