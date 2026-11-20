@extends('layouts.app')

@section('title', 'Fizzify - Log In')
@section('body_class', 'page-auth page-login')

@section('content')
<main class="auth" style="--bg: url('{{ asset('assets/img/bg-login.webp') }}')">
    <div class="auth-card">
        <div class="auth-card__photo" style="--photo: url('{{ asset('assets/img/bg-login.webp') }}')"></div>

        <div class="auth-card__main">
            <div class="auth-card__top">
                <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo-black.png') }}" alt="Fizzify"></a>
                <a class="pill-small" href="{{ route('admin.login') }}">Log In To Admin</a>
            </div>

            <form class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf
                <h1>Log In</h1>

                <div class="inputs">
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" required autofocus>
                    <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                </div>

                <div class="or">or</div>
                <a class="btn-google" href="{{ route('google.redirect') }}">
                    <img src="{{ asset('assets/img/google.webp') }}" alt="">Sign in with Google
                </a>
                <button class="btn-primary" type="submit">Login</button>

                <p class="switch">Didn't have an account? <a href="{{ route('signup') }}">Sign Up</a></p>
                <p class="switch"><a href="{{ route('home') }}">&larr; Kembali ke Home</a></p>
            </form>
        </div>
    </div>
</main>
@endsection
