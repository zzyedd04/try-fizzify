@extends('layouts.admin')

@section('title', 'Fizzify - Dashboard')
@section('body_class', 'admin--dashboard')

@section('content')
<main class="dash">
    <div class="dash__panel">

        {{-- Active Balance --}}
        <article class="dcard dcard--balance">
            <div class="dcard__row">
                <div>
                    <span class="lbl">Active Balance</span>
                    <div class="big">{{ rupiah($balance) }}</div>
                </div>
                <div class="acc">
                    <span class="lbl">Account Number</span>
                    <strong>{{ $accountNumber }}</strong>
                </div>
            </div>
            <div class="dcard__actions">
                <button class="chip" type="button" data-open="m-transfer">Transfer Balance</button>
                <button class="chip" type="button" data-open="m-request">Request Balance</button>
                <a class="circle" href="{{ route('admin.orders', ['range' => 'all']) }}" aria-label="Lihat semua order">&#8599;</a>
            </div>
        </article>

        {{-- Reward --}}
        <article class="dcard dcard--reward">
            <div class="dcard__row">
                <button class="chip chip--sm" type="button" id="priorityToggle" aria-pressed="false">PRIORITY <i class="dot"></i></button>
                <a class="circle circle--sm" href="{{ route('admin.products') }}" aria-label="Produk">&bull;&bull;&bull;</a>
            </div>
            <div class="reward">
                <span class="lbl">Your Reward</span>
                <div class="reward__row">
                    <strong class="mid">{{ rupiah($reward) }}</strong>
                    <span class="tag">{{ $rewardPct >= 0 ? '+' : '' }}{{ $rewardPct }}% This Month</span>
                </div>
            </div>
            <div class="tiles">
                <div class="tile"><span>Cans Sold</span><strong>{{ number_format($unitsSold, 0, ',', '.') }}</strong></div>
                <div class="tile"><span>Orders</span><strong>{{ number_format($ordersCount, 0, ',', '.') }}</strong></div>
            </div>
        </article>

        {{-- Spending --}}
        <article class="dcard dcard--spend">
            <div class="dcard__row">
                <span class="lbl lbl--lg">Spending</span>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <select class="month" name="month" data-autosubmit aria-label="Pilih bulan">
                        @foreach ($months as $value => $label)
                            <option value="{{ $value }}" {{ $month->format('Y-m') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="spend">
                <strong class="mid">{{ rupiah($monthSpending) }}</strong>
                <small>{{ $spendingPct >= 0 ? '+' : '' }}{{ $spendingPct }}%</small>
                <span class="tag tag--wide">
                    @if ($monthSpending > 0)
                        Penarikan bulan ini {{ $spendingPct >= 0 ? 'naik' : 'turun' }} {{ abs($spendingPct) }}%
                    @else
                        Belum ada penarikan bulan ini
                    @endif
                </span>
            </div>
            <ul class="breakdown">
                <li>Transfer {{ rupiah($monthTransfer) }}</li>
                <li>Request {{ rupiah($monthRequest) }}</li>
                <li>Saldo {{ rupiah($balance) }}</li>
                <li>Orders {{ $ordersCount }}</li>
            </ul>
        </article>

        {{-- Earning --}}
        <article class="dcard dcard--earn">
            <span class="lbl lbl--lg">Earning</span>
            <strong class="mid">{{ rupiah($monthRevenue) }}</strong>
            <small>{{ $revenuePct >= 0 ? '+' : '' }}{{ $revenuePct }}% VS Last Month</small>
            <svg class="spark" id="spark" viewBox="0 0 400 110" preserveAspectRatio="none" data-series='@json($daily)' aria-hidden="true"></svg>
            <a class="circle" href="{{ route('admin.products') }}" aria-label="Produk">&#8599;</a>
        </article>

        {{-- Transactions --}}
        <article class="dcard dcard--tx">
            <div class="dcard__row">
                <h3>Transactions</h3>
                <a class="circle circle--sm" href="{{ route('admin.orders') }}" aria-label="Semua order">&bull;&bull;&bull;</a>
            </div>

            <div class="txlist" id="txlist">
                @forelse ($transactions as $i => $t)
                    @php $isNew = $t->fulfillment_status === 'new'; @endphp
                    <div class="tx" data-new="{{ $isNew ? 1 : 0 }}">
                        <img class="tx__avatar" src="{{ asset('assets/img/avatar-'.['a', 'b', 'c'][$i % 3].'.webp') }}" alt="">
                        <div class="tx__who">
                            <strong>{{ $t->customer_name }}</strong>
                            <small>
                                @if ($t->user) {{ '@'.$t->user->username }} &middot; @endif
                                @if ($isNew) Payment Confirm &middot; {{ rupiah($t->total) }}
                                @else {{ $t->fulfillment_label }} &middot; {{ $t->paid_at->diffForHumans() }}
                                @endif
                            </small>
                        </div>

                        @if ($isNew)
                            <div class="tx__acts">
                                <form method="POST" action="{{ route('admin.orders.confirm', $t) }}">
                                    @csrf @method('PATCH')
                                    <button class="tx__ok" type="submit" title="Konfirmasi pesanan" aria-label="Konfirmasi">&#10003;</button>
                                </form>
                                <form method="POST" action="{{ route('admin.orders.cancel', $t) }}" onsubmit="return confirm('Batalkan pesanan ini? Refund dilakukan manual di dashboard Midtrans.')">
                                    @csrf @method('PATCH')
                                    <button class="tx__no" type="submit" title="Batalkan pesanan" aria-label="Batalkan">&#10005;</button>
                                </form>
                            </div>
                        @else
                            <span class="tx__amt">{{ rupiah($t->total) }}</span>
                        @endif
                    </div>
                @empty
                    @foreach ([1, 2, 3] as $n)
                        <div class="tx tx--empty">
                            <img class="tx__avatar" src="{{ asset('assets/img/avatar-'.['a', 'b', 'c'][$n - 1].'.webp') }}" alt="">
                            <div class="tx__who"><strong>customer {{ $n }}</strong><small>Menunggu pembeli</small></div>
                            <span class="tx__amt">Rp. 0</span>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </article>
    </div>
</main>

{{-- Modal: Transfer / Request Balance --}}
@foreach (['transfer' => 'Transfer Balance', 'request' => 'Request Balance'] as $type => $title)
<div class="amodal" id="m-{{ $type }}" hidden>
    <div class="amodal__backdrop" data-aclose></div>
    <div class="amodal__box" role="dialog" aria-modal="true">
        <button class="amodal__x" type="button" data-aclose aria-label="Tutup">&times;</button>
        <h2>{{ $title }}</h2>
        <p class="amodal__sub">
            Saldo aktif <strong>{{ rupiah($balance) }}</strong>
            @if ($type === 'transfer') &middot; ditarik langsung ke rekening {{ $accountNumber }}
            @else &middot; saldo ditahan sampai request ditandai selesai
            @endif
        </p>

        <form method="POST" action="{{ route('admin.withdrawals.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <label>Jumlah (Rp)
                <input type="number" name="amount" min="1000" step="1000" max="{{ max($balance, 1000) }}" placeholder="Contoh: 50000" required>
            </label>
            <label>Catatan (opsional)
                <input type="text" name="note" maxlength="120">
            </label>
            <button class="amodal__go" type="submit" {{ $balance < 1000 ? 'disabled' : '' }}>
                {{ $balance < 1000 ? 'Saldo belum cukup' : $title }}
            </button>
        </form>

        @if ($type === 'request' && $withdrawals->isNotEmpty())
            <h4>Riwayat penarikan</h4>
            <ul class="wlist">
                @foreach ($withdrawals as $w)
                    <li>
                        <span>{{ ucfirst($w->type) }} &middot; {{ rupiah($w->amount) }}</span>
                        <em class="{{ $w->status }}">{{ $w->status === 'done' ? 'Selesai' : 'Pending' }}</em>
                        @if ($w->status === 'pending')
                            <form method="POST" action="{{ route('admin.withdrawals.complete', $w) }}">@csrf @method('PATCH')<button type="submit">Selesaikan</button></form>
                            <form method="POST" action="{{ route('admin.withdrawals.destroy', $w) }}">@csrf @method('DELETE')<button type="submit" class="danger">Batalkan</button></form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endforeach
@endsection
