<header class="nav">
    <a class="nav__logo" href="{{ route('home') }}" aria-label="Fizzify">
        <img class="logo-green" src="{{ asset('assets/img/logo-green.png') }}" alt="Fizzify">
        <img class="logo-black" src="{{ asset('assets/img/logo-black.png') }}" alt="Fizzify">
    </a>

    <nav class="nav__pill">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Home</a>
        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}">About</a>
        <a href="{{ route('product') }}" class="{{ request()->routeIs('product') ? 'is-active' : '' }}">Product</a>
        <a href="{{ route('portfolio') }}" class="{{ request()->routeIs('portfolio') ? 'is-active' : '' }}">Portofolio</a>
        <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}">Contact</a>
    </nav>

    <div class="nav__auth">
        @php $cartCount = auth()->check() ? auth()->user()->orders()->inCart()->count() : 0; @endphp
        <a class="nav__cart {{ request()->routeIs('cart') ? 'is-active' : '' }}" href="{{ route('cart') }}" aria-label="Keranjang" title="Keranjang">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 7h12l-1 13H7L6 7Z"/>
                <path d="M9 7a3 3 0 0 1 6 0"/>
            </svg>
            @if ($cartCount > 0)
                <span class="nav__cartn">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
            @endif
        </a>
        @auth
            <div class="nav__user">
                <span class="nav__avatar">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</span>
                <span>{{ auth()->user()->username }}</span>
                @if (auth()->user()->isAdmin())
                    <a class="btn-out" href="{{ route('admin.dashboard') }}">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-out" type="submit">Logout</button>
                </form>
            </div>
        @else
            <a class="btn-login" href="{{ route('login') }}">Login</a>
        @endauth
    </div>
</header>
