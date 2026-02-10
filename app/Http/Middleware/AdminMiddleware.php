<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Middleware untuk memastikan hanya admin yang bisa akses route tertentu
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah sudah login sebagai admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
