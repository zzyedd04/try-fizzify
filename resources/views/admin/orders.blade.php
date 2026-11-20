@extends('layouts.admin')

@section('title', 'Fizzify - Orders')
@section('body_class', 'admin--orders')

@section('content')
<main class="aord">
    <section class="aord__left">
        <h1>Order For<br>Today</h1>
        <div class="aord__tabs">
            <a href="{{ route('admin.orders') }}" class="{{ $range === 'today' ? 'is-on' : '' }}">Hari ini ({{ $todayCount }})</a>
            <a href="{{ route('admin.orders', ['range' => 'all']) }}" class="{{ $range === 'all' ? 'is-on' : '' }}">Semua ({{ $allCount }})</a>
        </div>
        <img class="aord__photo" src="{{ asset('assets/img/bg-login.webp') }}" alt="Fizzify">
    </section>

    <section class="aord__panel">
        @forelse ($orders as $o)
            <article class="orow" tabindex="0" role="button" data-open="order-{{ $o->id }}">
                <img class="orow__avatar" src="{{ $o->avatar_url }}" alt="">
                <div class="orow__who">
                    <strong>{{ $o->customer_name }}@if ($o->user) <small style="font-weight:600;opacity:.65">{{ '@'.$o->user->username }}</small>@endif</strong>
                    <span><img src="{{ asset('assets/img/icon-location.webp') }}" alt="">{{ \Illuminate\Support\Str::startsWith($o->address, 'Indonesia') ? $o->address : 'Indonesia, '.$o->address }}</span>
                </div>
                <span class="orow__status is-{{ $o->fulfillment_status }}">{{ $o->fulfillment_label }}</span>
                <div class="orow__can" style="--c: {{ $o->product->color }}">
                    <img src="{{ $o->product->image_url }}" alt="">
                    <strong>{{ $o->product->name }}@if ($o->quantity > 1) <small>x{{ $o->quantity }}</small>@endif</strong>
                </div>
            </article>
        @empty
            <div class="aord__empty">
                <strong>Belum ada pesanan {{ $range === 'today' ? 'hari ini' : '' }}</strong>
                <p>Pesanan muncul otomatis setelah pembeli menyelesaikan pembayaran lewat Midtrans.</p>
            </div>
        @endforelse
    </section>
</main>

@foreach ($orders as $o)
<div class="amodal" id="order-{{ $o->id }}" hidden>
    <div class="amodal__backdrop" data-aclose></div>
    <div class="amodal__box" role="dialog" aria-modal="true">
        <button class="amodal__x" type="button" data-aclose aria-label="Tutup">&times;</button>
        <h2>{{ $o->customer_name }}</h2>
        <p class="amodal__sub">{{ $o->code }} &middot; {{ $o->paid_at->translatedFormat('d M Y, H:i') }}</p>

        <dl class="odetail">
            <dt>Akun</dt><dd>{{ $o->user ? '@'.$o->user->username : '-' }}@if ($o->user?->email) &middot; {{ $o->user->email }}@endif</dd>
            <dt>Produk</dt><dd>{{ $o->product->name }} x{{ $o->quantity }}</dd>
            <dt>Total</dt><dd>{{ rupiah($o->total) }}</dd>
            <dt>Pembayaran</dt><dd>{{ str_replace('_', ' ', $o->payment_type ?? 'Midtrans') }}</dd>
            <dt>No. HP</dt><dd><a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D+/', '', $o->phone)) }}" target="_blank" rel="noopener">{{ $o->phone }}</a></dd>
            <dt>Alamat</dt><dd>{{ $o->address }}</dd>
            <dt>Status</dt><dd>{{ $o->fulfillment_label }}</dd>
        </dl>

        @if ($o->fulfillment_status === 'new')
            <div class="odetail__acts">
                <form method="POST" action="{{ route('admin.orders.confirm', $o) }}">@csrf @method('PATCH')<button class="amodal__go" type="submit">Konfirmasi</button></form>
                <form method="POST" action="{{ route('admin.orders.cancel', $o) }}" onsubmit="return confirm('Batalkan pesanan ini?')">@csrf @method('PATCH')<button class="amodal__go amodal__go--danger" type="submit">Batalkan</button></form>
            </div>
        @endif
    </div>
</div>
@endforeach
@endsection
