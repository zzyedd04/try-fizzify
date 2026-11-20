<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin(Request $request)
    {
        // Datang dari tombol Buy tapi belum login: setelah login kembali ke halaman produk
        if ($request->boolean('buy')) {
            session(['url.intended' => route('product')]);
            session()->flash('info', 'Login dulu ya untuk membeli Fizzify.');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $user = $this->attempt($request);

        if (! $user) {
            return back()->withInput($request->only('username'))
                ->with('error', 'Username atau password salah. Belum punya akun? Klik Sign Up.');
        }

        if ($user->isAdmin()) {
            return back()->withInput($request->only('username'))
                ->with('error', 'Ini akun admin. Silakan pakai tombol "Log In To Admin".');
        }

        $this->signIn($request, $user);

        return redirect()->intended(route('home'));
    }

    public function showAdminLogin()
    {
        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {
        $user = $this->attempt($request);

        if (! $user || ! $user->isAdmin()) {
            return back()->withInput($request->only('username'))
                ->with('error', 'Login admin gagal. Periksa username dan password admin.');
        }

        $this->signIn($request, $user);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $wasAdmin = $request->user()?->isAdmin();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($wasAdmin ? 'admin.login' : 'home');
    }

    /** Cari user berdasarkan username atau email lalu cocokkan password */
    private function attempt(Request $request): ?User
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $data['username'])->orWhere('email', $data['username'])->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    private function signIn(Request $request, User $user): void
    {
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
    }
}
