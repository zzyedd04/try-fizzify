<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.signup');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'username.unique' => 'Username sudah dipakai',
            'username.min' => 'Username minimal 3 karakter',
            'password.min' => 'Password minimal 4 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sama',
        ]);

        User::create([
            'name' => $data['username'],
            'username' => $data['username'],
            'password' => $data['password'],
            'role' => 'user',
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }
}
