@extends('layouts.admin')

@section('title', 'Fizzify - Products')
@section('body_class', 'admin--products')

@section('content')
<main class="aprod">
    <div class="aprod__stats">
        <div class="stat"><span>Total Produk</span><strong>{{ $products->count() }}</strong></div>
        <div class="stat"><span>Total Terjual</span><strong>{{ number_format($totalSold, 0, ',', '.') }}</strong></div>
        <div class="stat"><span>Total Stok</span><strong>{{ number_format($totalStock, 0, ',', '.') }}</strong></div>
        <div class="stat"><span>Terlaris</span><strong>{{ $bestSeller?->name ?? '-' }}</strong></div>
    </div>

    <div class="aprod__grid">
        @foreach ($products as $p)
            @php $pct = min(100, (int) round($p->stock / 1000 * 100)); @endphp
            <article class="acard {{ $p->is_active ? '' : 'is-off' }}" style="--c: {{ $p->color }}">
                <div class="acard__img"><img src="{{ $p->image_url }}" alt="{{ $p->name }}"></div>

                <div class="acard__info">
                    <h3>{{ $p->name }}</h3>
                    <p class="acard__price">{{ rupiah($p->price) }}</p>
                    <p class="acard__desc">{{ $p->description }}</p>

                    <div class="acard__meta">
                        <span>Stok {{ number_format($p->stock, 0, ',', '.') }}</span>
                        <span>Omzet {{ rupiah($revenue[$p->id] ?? 0) }}</span>
                    </div>
                    <div class="bar"><i style="width: {{ $pct }}%"></i></div>

                    <div class="acard__foot">
                        <span class="sold">{{ number_format($p->sold, 0, ',', '.') }} Terjual</span>
                        <button class="edit" type="button" data-open="edit-{{ $p->id }}">Edit</button>
                    </div>
                </div>
            </article>

            <div class="amodal" id="edit-{{ $p->id }}" hidden>
                <div class="amodal__backdrop" data-aclose></div>
                <div class="amodal__box" role="dialog" aria-modal="true">
                    <button class="amodal__x" type="button" data-aclose aria-label="Tutup">&times;</button>
                    <h2 style="color: {{ $p->color }}">{{ $p->name }}</h2>
                    <p class="amodal__sub">{{ number_format($p->sold, 0, ',', '.') }} terjual. Angka ini hanya naik saat ada pembayaran Midtrans yang berhasil.</p>

                    <form method="POST" action="{{ route('admin.products.update', $p) }}">
                        @csrf @method('PATCH')
                        <label>Harga (Rp)
                            <input type="number" name="price" value="{{ $p->price }}" min="1000" step="500" required>
                        </label>
                        <label>Stok
                            <input type="number" name="stock" value="{{ $p->stock }}" min="0" required>
                        </label>
                        <label class="check">
                            <input type="checkbox" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}>
                            Tampilkan di halaman Product
                        </label>
                        <button class="amodal__go" type="submit">Simpan</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</main>
@endsection
