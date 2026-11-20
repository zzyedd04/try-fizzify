<?php

use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);

        // Midtrans mengirim notifikasi dari server mereka, jadi tidak membawa token CSRF
        $middleware->validateCsrfTokens(except: [
            'midtrans/notification',
        ]);

        // Tamu yang membuka halaman terlindungi diarahkan ke halaman login yang sesuai
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin*')
            ? route('admin.login')
            : route('login'));

        // User yang sudah login tidak perlu melihat halaman login lagi
        $middleware->redirectUsersTo(fn ($request) => auth()->user()?->isAdmin()
            ? route('admin.dashboard')
            : route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
