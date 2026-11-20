@extends('layouts.app')

@section('title', 'Fizzify - Home')
@section('body_class', 'page-home')

@section('content')
<main class="hero">
    <div class="hero__content">
        <h1 class="hero__title">Pop the fizz<br>feel the fun!</h1>

        <div class="hero__text glass">
            <p>
                Fizzify hadir buat bikin harimu makin berwarna dan penuh energi! Kami memadukan kesegaran soda
                dengan varian rasa buah favoritmu, siap nemenin setiap momen santai, kumpul bareng teman, sampai
                petualangan serumu berikutnya. <strong>Pop the fizz and feel the fun!</strong>
            </p>
        </div>

        <a class="cta" href="{{ route('product') }}">
            <span>Explore our product</span>
            @include('partials.arrow')
        </a>
    </div>
</main>
@endsection
