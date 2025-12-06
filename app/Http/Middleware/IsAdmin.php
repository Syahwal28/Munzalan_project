<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user LOGIN dan ROLE-nya 'admin'
        if (Auth::check() && Auth::user()->role_user === 'admin') {
            return $next($request); // Silakan lewat
        }

        // Jika bukan admin, tolak akses (Error 403 Forbidden)
        abort(403, 'AKSES DITOLAK. Halaman ini khusus Admin.');
    }
}