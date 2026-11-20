@extends('layouts.app')

@section('title', 'Fizzify - About')
@section('body_class', 'page-about')

@section('content')
<main class="hero">
    <div class="hero__content">
        <h1 class="hero__title">About us</h1>

        <div class="hero__text glass">
            <p>
                Fizzify lahir dari ide sederhana: menghadirkan minuman yang nggak cuma menghilangkan dahaga, tapi
                juga membawa keceriaan di setiap tegukan. Kami percaya bahwa setiap harimu berhak dirayakan dengan
                sesuatu yang menyegarkan dan penuh warna.
            </p>
            <p>
                Dengan memadukan rasa buah pilihan dan gelembung soda yang pas, Fizzify hadir dalam berbagai
                varian rasa unik yang siap menemani setiap momenmu&mdash;mulai dari fokus ngerjain tugas, nongkrong
                bareng teman, sampai santai di akhir pekan.
            </p>
        </div>

        <a class="cta" href="{{ route('portfolio') }}">
            <span>Explore our portofolio</span>
            @include('partials.arrow')
        </a>
    </div>
</main>
@endsection
