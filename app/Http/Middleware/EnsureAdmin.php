<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if (! $user->isAdmin()) {
            return redirect()->route('admin.login')
                ->with('error', 'Halaman ini khusus admin. Silakan login sebagai admin.');
        }

        return $next($request);
    }
}
