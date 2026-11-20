@extends('layouts.app')

@section('title', 'Fizzify - Portofolio')
@section('body_class', 'page-portfolio')

@section('content')
<main class="portfolio">
    <section>
        <h1 class="portfolio__title">Our<br>Portofolio</h1>

        <div class="portfolio__cards">
            <div class="glass">
                Intip rekam jejak momen seru dan kesegaran nyata bersama Fizzify! Dari aktivitas olahraga,
                nongkrong bareng teman, hingga selebrasi santai di kolam renang, Fizzify selalu hadir
                mewarnai setiap harimu.
            </div>
            <div class="glass">
                Koleksi visual dan kampanye kreatif yang menampilkan semangat penuh warna Fizzify. Kami
                menghadirkan kombinasi rasa unik dan energi positif yang menyatu sempurna dengan gaya
                hidup aktif masa kini.
            </div>
        </div>
    </section>

    <section class="gallery">
        @foreach (range(1, 6) as $n)
            <button class="gallery__item" type="button" data-lightbox="{{ asset('assets/img/portfolio-'.$n.'.webp') }}" aria-label="Lihat foto {{ $n }}">
                <img src="{{ asset('assets/img/portfolio-'.$n.'.webp') }}" alt="Portofolio Fizzify {{ $n }}" loading="lazy">
            </button>
        @endforeach
    </section>
</main>
@endsection
