@extends('layouts.app')

@section('title', 'Fizzify - Sign Up')
@section('body_class', 'page-auth page-signup')

@section('content')
<main class="auth" style="--bg: url('{{ asset('assets/img/bg-home.webp') }}')">
    <div class="auth-card">
        <div class="auth-card__photo" style="--photo: url('{{ asset('assets/img/bg-home.webp') }}'); --pos: 38% center"></div>

        <div class="auth-card__main">
            <div class="auth-card__top">
                <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo-black.png') }}" alt="Fizzify"></a>
                <a class="pill-small" href="{{ route('login') }}">Log In</a>
            </div>

            <form class="auth-form" method="POST" action="{{ route('signup.store') }}">
                @csrf
                <h1>Sign Up</h1>

                <div class="inputs">
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" required autofocus>
                    <input type="password" name="password" placeholder="Password" autocomplete="new-password" required>
                    <input type="password" name="password_confirmation" placeholder="Confirm your password" autocomplete="new-password" required>
                </div>

                <div class="or">or</div>
                <a class="btn-google" href="{{ route('google.redirect') }}">
                    <img src="{{ asset('assets/img/google.webp') }}" alt="">Sign in with Google
                </a>
                <button class="btn-primary" type="submit">Sign Up</button>

                <p class="switch">Already had an account? <a href="{{ route('login') }}">Log In</a></p>
            </form>
        </div>
    </div>
</main>
@endsection
