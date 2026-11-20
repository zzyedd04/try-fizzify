<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /** /auth/google  (tambahkan ?as=admin dari halaman login admin) */
    public function redirect(Request $request)
    {
        $as = $request->query('as') === 'admin' ? 'admin' : 'user';
        $back = $as === 'admin' ? 'admin.login' : 'login';

        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()->route($back)->with('error', 'Login Google belum aktif. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env (lihat README).');
        }

        session(['google_as' => $as]);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        $as = session()->pull('google_as', 'user');

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route($as === 'admin' ? 'admin.login' : 'login')
                ->with('error', 'Login dengan Google gagal, silakan coba lagi.');
        }

        $email = strtolower((string) $google->getEmail());
        $adminEmails = array_map('strtolower', config('fizzify.admin_emails'));
        $isAdminEmail = in_array($email, $adminEmails, true);

        if ($as === 'admin' && ! $isAdminEmail) {
            return redirect()->route('admin.login')
                ->with('error', 'Email Google ini belum terdaftar sebagai admin (lihat ADMIN_EMAILS di .env).');
        }

        $user = User::where('google_id', $google->getId())->orWhere('email', $email)->first();

        if (! $user) {
            $user = new User([
                'name' => $google->getName() ?: Str::before($email, '@'),
                'username' => $this->uniqueUsername($email),
                'role' => 'user',
            ]);
        }

        $user->email = $email;
        $user->google_id = $google->getId();
        $user->avatar = $google->getAvatar();

        if ($as === 'admin' && $isAdminEmail) {
            $user->role = 'admin';
        }

        $user->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->intended(route('home'));
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '') ?: 'user';
        $username = $base;
        $i = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.(++$i);
        }

        return $username;
    }
}
