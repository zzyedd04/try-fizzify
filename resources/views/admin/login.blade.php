@extends('layouts.app')

@section('title', 'Fizzify - Admin Login')
@section('body_class', 'page-auth page-adminlogin')

@section('content')
<main class="auth" style="--bg: url('{{ asset('assets/img/bg-admin-login.webp') }}')">
    <div class="auth-card">
        <div class="auth-card__photo" style="--photo: url('{{ asset('assets/img/bg-admin-login.webp') }}')"></div>

        <div class="auth-card__main">
            <div class="auth-card__top">
                <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo-black.png') }}" alt="Fizzify"></a>
                <a class="pill-small" href="{{ route('login') }}">Log In As User</a>
            </div>

            <form class="auth-form" method="POST" action="{{ route('admin.login') }}">
                @csrf
                <h1>Welcome Chief!</h1>
                <p class="sub">Welcome Back Sir</p>

                <div class="inputs">
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" required autofocus>
                    <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                </div>

                <div class="or">or</div>
                <a class="btn-google" href="{{ route('google.redirect', ['as' => 'admin']) }}">
                    <img src="{{ asset('assets/img/google.webp') }}" alt="">Sign in with Google
                </a>
                <button class="btn-primary" type="submit">Login</button>

                <p class="switch"><a href="{{ route('home') }}">&larr; Kembali ke Home</a></p>
            </form>
        </div>
    </div>
</main>
@endsection
