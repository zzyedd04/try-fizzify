<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fizzify')</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="@yield('body_class')">

    @include('partials.nav')

    @yield('content')

    @include('partials.flash')

    {{-- skrip halaman (konfigurasi Midtrans, dll.) harus dimuat sebelum app.js --}}
    @yield('scripts')

    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
