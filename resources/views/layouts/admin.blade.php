<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fizzify Admin')</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body class="admin @yield('body_class')">

    <header class="anav">
        <a class="anav__logo" href="{{ route('admin.dashboard') }}" aria-label="Dashboard">
            <img class="logo-black" src="{{ asset('assets/img/logo-black.png') }}" alt="Fizzify">
            <img class="logo-green" src="{{ asset('assets/img/logo-green.png') }}" alt="Fizzify">
        </a>

        <nav class="anav__pill">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.products') }}" class="{{ request()->routeIs('admin.products') ? 'is-active' : '' }}">Products</a>
            <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders') ? 'is-active' : '' }}">Orders</a>
        </nav>

        <div class="anav__right">
            <a class="anav__site" href="{{ route('home') }}" target="_blank" rel="noopener">Lihat Website</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="anav__out" type="submit">Logout</button>
            </form>
        </div>
    </header>

    @yield('content')

    @include('partials.flash')

    {{-- skrip halaman (konfigurasi Midtrans, dll.) harus dimuat sebelum app.js --}}
    @yield('scripts')

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
