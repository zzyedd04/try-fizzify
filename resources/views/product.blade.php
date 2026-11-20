@extends('layouts.app')

@section('title', 'Fizzify - Product')
@section('body_class', 'page-product')

@section('content')
<main class="product">
    <div class="shop-grid">
        @foreach ($products as $p)
            <article class="pcard" style="--c: {{ $p->color }}">
                <div class="pcard__img"><img src="{{ $p->image_url }}" alt="{{ $p->name }}"></div>
                <h3 class="pcard__name">{{ $p->name }}</h3>
                <p class="pcard__desc">&ldquo;{{ $p->description }}&rdquo;</p>
                <span class="pcard__price">{{ rupiah($p->price) }}</span>

                @if ($p->stock < 1)
                    <span class="pcard__buy is-soldout">Habis</span>
                @else
                    @auth
                        <button class="pcard__buy" type="button" data-buy
                                data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}"
                                data-image="{{ $p->image_url }}" data-stock="{{ $p->stock }}">Buy</button>
                    @else
                        <a class="pcard__buy" href="{{ route('login', ['buy' => 1]) }}">Buy</a>
                    @endauth
                @endif
            </article>
        @endforeach
    </div>
</main>

@auth
<div class="modal" id="checkout" hidden>
    <div class="modal__backdrop" data-close></div>
    <div class="pay" role="dialog" aria-modal="true" aria-labelledby="payTitle">
        <button class="pay__close" type="button" data-close aria-label="Tutup">&times;</button>

        <div class="pay__left">
            <h2 id="payTitle">Payment</h2>

            <form id="checkoutForm">
                <input type="hidden" name="product_id" id="payProductId">

                <div class="pay__head">
                    <span>Detail Pesanan</span>
                    <img src="{{ asset('assets/img/pay-cards.webp') }}" alt="Visa, Mastercard, AMEX, JCB">
                </div>

                <label>Nama Lengkap
                    <input type="text" name="customer_name" value="{{ auth()->user()->name }}" required maxlength="80">
                </label>
                <label>No. HP / WhatsApp
                    <input type="tel" name="phone" inputmode="tel" placeholder="08xxxxxxxxxx" required maxlength="20">
                </label>
                <label>Alamat / Lokasi
                    <input type="text" name="address" placeholder="Contoh: Banda Aceh, Meuraxa" required maxlength="200">
                </label>

                <div class="pay__qty">
                    <span>Jumlah</span>
                    <div class="stepper">
                        <button type="button" data-qty="-1" aria-label="Kurangi">&minus;</button>
                        <output id="qtyOut">1</output>
                        <button type="button" data-qty="1" aria-label="Tambah">+</button>
                    </div>
                </div>

                <p class="pay__note">
                    Metode bayar dipilih di langkah berikutnya: Kartu Kredit/Debit, Virtual Account, QRIS,
                    GoPay, ShopeePay, dan lainnya. Pembayaran diproses aman oleh Midtrans.
                </p>

                <div class="pay__due"><span>Due Today</span><strong id="dueOut">Rp. 0</strong></div>
                <button class="pay__buy" id="payBtn" type="submit">Buy Now</button>
            </form>
        </div>

        <div class="pay__right">
            <div class="pay__can"><img id="payImg" src="" alt=""></div>
            <h3 id="payName"></h3>
            <p id="payPrice"></p>
        </div>
    </div>
</div>

<div class="modal" id="paidOk" hidden>
    <div class="modal__backdrop" data-close></div>
    <div class="ok" role="alertdialog">
        <button type="button" data-close aria-label="Tutup">&times;</button>
        <strong>Payment Successful</strong>
        <span>Thank You</span>
        <a class="ok__link" href="{{ route('cart') }}">Lihat pesanan di Keranjang</a>
    </div>
</div>
@endauth
@endsection

@section('scripts')
@auth
    <script>
        window.FZ = {
            checkoutUrl: @json(route('checkout.store')),
            syncUrl: @json(url('/checkout')),
            midtransReady: @json($midtransReady),
        };
    </script>
    @if ($midtransReady)
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    @endif
@endauth
@endsection
