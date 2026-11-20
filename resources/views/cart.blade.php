@extends('layouts.app')

@section('title', 'Fizzify - Keranjang')
@section('body_class', 'page-cart')

@section('content')
<main class="product cart">
    <div class="cart__wrap">
        <h1 class="cart__title">Keranjang Saya</h1>

        @forelse ($orders as $o)
            @php
                $paid = $o->payment_status === 'paid';
                if (! $paid) {
                    $state = 'pending';
                    $label = 'Menunggu pembayaran';
                } elseif ($o->fulfillment_status === 'cancelled') {
                    $state = 'cancelled';
                    $label = 'Dibatalkan';
                } elseif ($o->fulfillment_status === 'confirmed') {
                    $state = 'confirmed';
                    $label = 'Pesanan dikonfirmasi';
                } else {
                    $state = 'paid';
                    $label = 'Dibayar';
                }
            @endphp
            <article class="corder" style="--c: {{ $o->product->color }}">
                <div class="corder__img"><img src="{{ $o->product->image_url }}" alt="{{ $o->product->name }}"></div>

                <div class="corder__info">
                    <h3>{{ $o->product->name }} <small>x{{ $o->quantity }}</small></h3>
                    <p class="corder__meta">{{ $o->code }} &middot; {{ ($o->paid_at ?? $o->created_at)->translatedFormat('d M Y, H:i') }}</p>
                    <p class="corder__meta">{{ $o->customer_name }} &middot; {{ $o->address }}</p>
                </div>

                <div class="corder__side">
                    <strong class="corder__total">{{ rupiah($o->total) }}</strong>
                    <span class="corder__status is-{{ $state }}">{{ $label }}</span>
                    @if (! $paid && $midtransReady)
                        <button class="corder__pay" type="button" data-pay="{{ $o->code }}" data-token="{{ $o->snap_token }}">Bayar Sekarang</button>
                    @endif
                </div>
            </article>
        @empty
            <div class="cart__empty">
                <strong>Keranjang masih kosong</strong>
                <p>Pesanan yang kamu beli akan muncul di sini setelah pembayaran lewat Midtrans.</p>
                <a class="pcard__buy" href="{{ route('product') }}" style="--c: var(--blue-deep)">Belanja Sekarang</a>
            </div>
        @endforelse
    </div>
</main>
@endsection

@section('scripts')
    @if ($midtransReady)
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    @endif
    <script>
        // "Bayar Sekarang" untuk pesanan yang belum dibayar: buka lagi popup Midtrans dengan token yang sama
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var syncUrl = @json(url('/checkout'));

            document.querySelectorAll('[data-pay]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!window.snap) {
                        alert('Midtrans belum siap. Muat ulang halaman lalu coba lagi.');
                        return;
                    }

                    var code = btn.dataset.pay;
                    var done = false;

                    function finish() {
                        if (done) return;
                        done = true;
                        fetch(syncUrl + '/' + code + '/sync', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        }).finally(function () { location.reload(); });
                    }

                    window.snap.pay(btn.dataset.token, {
                        onSuccess: finish,
                        onPending: finish,
                        onError: finish,
                        onClose: finish,
                    });
                });
            });
        })();
    </script>
@endsection
