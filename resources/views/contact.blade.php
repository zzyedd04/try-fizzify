@extends('layouts.app')

@section('title', 'Fizzify - Contact')
@section('body_class', 'page-contact theme-light')

@section('content')
<main class="contact">
    <h1 class="contact__title">Contact Us</h1>

    <div class="contact__body">
        <div class="info">
            <div class="info__row">
                <img src="{{ asset('assets/img/icon-location.webp') }}" alt="">
                <a href="https://www.google.com/maps/search/?api=1&query=Banda+Aceh" target="_blank" rel="noopener">Indonesia, Banda Aceh<br>2026</a>
            </div>
            <div class="info__row">
                <img src="{{ asset('assets/img/icon-clock.webp') }}" alt="">
                <span>Operational<br>08.00 AM - 22.00 PM</span>
            </div>
            <div class="info__row">
                <img src="{{ asset('assets/img/icon-mail.webp') }}" alt="">
                <a href="mailto:{{ config('fizzify.email') }}">Email<br>{{ config('fizzify.email') }}</a>
            </div>
        </div>

        <form class="form" method="POST" action="{{ route('contact.store') }}">
            @csrf
            <h2>Personal Information</h2>

            <div class="form__grid">
                <label class="field"><input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="First Name" required></label>
                <label class="field"><input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Last Name"></label>
                <label class="field"><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Phone Number" required></label>
                <label class="field"><input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required></label>
                <label class="field field--wide"><input type="text" name="message" value="{{ old('message') }}" placeholder="Project Description" required></label>
            </div>

            <div class="form__submit">
                <button class="btn-submit" type="submit">Submit</button>
                <span class="wa-hint">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a9.9 9.9 0 0 0-8.4 15.1L2 22l5.05-1.6A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.25.7-1.45 1.33-2 1.4-.52.08-1.17.1-1.9-.12a17 17 0 0 1-1.72-.64c-3.03-1.3-5-4.35-5.15-4.55-.15-.2-1.23-1.63-1.23-3.1 0-1.48.77-2.2 1.05-2.5.27-.3.6-.37.8-.37h.57c.18 0 .43-.07.67.52.25.6.85 2.07.92 2.22.08.15.13.32.03.52-.1.2-.15.32-.3.5-.15.17-.32.38-.45.52-.15.15-.3.31-.13.6.18.3.77 1.26 1.65 2.04 1.13 1 2.08 1.3 2.38 1.45.3.15.47.12.65-.07.17-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.27.1 1.73.82 2.03.97.3.15.5.22.57.35.07.12.07.72-.18 1.42Z"/></svg>
                    Pesanmu akan dikirim lewat WhatsApp
                </span>
            </div>
        </form>
    </div>

    <footer class="footer">
        <div class="footer__cols">
            <span></span>
            <div class="footer__col"><strong>Mount Highland</strong>Pop the fizz,<br>feel the fun!</div>
            <div class="footer__col"><strong>Mount Highland</strong>Segar di setiap<br>tegukan</div>
            <div class="footer__col"><strong>Mount Highland</strong>Rasa buah, energi<br>penuh warna</div>
            <div class="footer__contact">
                <a href="https://wa.me/{{ config('fizzify.whatsapp') }}" target="_blank" rel="noopener">{{ config('fizzify.whatsapp_display') }}</a>
                <a href="mailto:{{ config('fizzify.email') }}">{{ config('fizzify.email') }}</a>
            </div>
        </div>

        <img class="footer__logo footer__logo--l" src="{{ asset('assets/img/logo-green.png') }}" alt="">
        <img class="footer__logo footer__logo--r" src="{{ asset('assets/img/logo-green.png') }}" alt="">
        <p class="footer__copy">Copyright {{ date('Y') }}</p>
    </footer>
</main>
@endsection
