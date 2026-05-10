<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirect user yang sudah login ke dashboard sesuai role-nya.
     * Mencegah user yang sudah login mengakses halaman /login, /register, dll.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect berdasarkan role
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                // Default: user biasa ke home
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
