<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Withdrawal;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, MidtransService $midtrans)
    {
        // Ambil status pembayaran terbaru dari Midtrans dulu, supaya saldo & omzet selalu mutakhir
        $midtrans->syncPending();

        $month = $this->selectedMonth($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        // ---- Saldo: hanya dari pesanan yang benar-benar dibayar lewat Midtrans ----
        $revenueAll = (int) Order::revenue()->sum('total');
        $transferred = (int) Withdrawal::where('status', 'done')->sum('amount');
        $reserved = (int) Withdrawal::where('status', 'pending')->sum('amount');
        $balance = $revenueAll - $transferred - $reserved;

        // ---- Omzet bulan terpilih vs bulan sebelumnya ----
        $monthRevenue = (int) Order::revenue()->whereBetween('paid_at', [$start, $end])->sum('total');
        $prevRevenue = (int) Order::revenue()->whereBetween('paid_at', [$prevStart, $prevEnd])->sum('total');

        // ---- Penarikan bulan terpilih ----
        $monthTransfer = (int) Withdrawal::where('status', 'done')->whereBetween('created_at', [$start, $end])->sum('amount');
        $monthRequest = (int) Withdrawal::where('status', 'pending')->whereBetween('created_at', [$start, $end])->sum('amount');
        $monthSpending = $monthTransfer + $monthRequest;
        $prevSpending = (int) Withdrawal::whereBetween('created_at', [$prevStart, $prevEnd])->sum('amount');

        // ---- Statistik umum ----
        $unitsSold = (int) Order::revenue()->sum('quantity');
        $ordersCount = Order::revenue()->count();

        // ---- Omzet per hari untuk grafik Earning ----
        $daily = array_fill(1, $month->daysInMonth, 0);
        Order::revenue()->whereBetween('paid_at', [$start, $end])->get(['paid_at', 'total'])
            ->each(function ($order) use (&$daily) {
                $daily[(int) $order->paid_at->format('j')] += $order->total;
            });

        // ---- Daftar pilihan bulan (6 bulan terakhir) ----
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $m = now()->startOfMonth()->subMonthsNoOverflow($i);
            $months[$m->format('Y-m')] = $m->translatedFormat('F');
        }

        return view('admin.dashboard', [
            'month' => $month,
            'months' => $months,
            'accountNumber' => config('fizzify.account_number'),
            'balance' => $balance,
            'reward' => (int) round($monthRevenue * config('fizzify.reward_rate')),
            'rewardPct' => $this->percent($monthRevenue, $prevRevenue),
            'unitsSold' => $unitsSold,
            'ordersCount' => $ordersCount,
            'monthRevenue' => $monthRevenue,
            'revenuePct' => $this->percent($monthRevenue, $prevRevenue),
            'monthSpending' => $monthSpending,
            'spendingPct' => $this->percent($monthSpending, $prevSpending),
            'monthTransfer' => $monthTransfer,
            'monthRequest' => $monthRequest,
            'daily' => array_values($daily),
            'transactions' => Order::paid()->with(['product', 'user'])->latest('paid_at')->limit(8)->get(),
            'withdrawals' => Withdrawal::latest()->limit(6)->get(),
        ]);
    }

    private function selectedMonth(?string $value): Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth() : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    private function percent(int $current, int $previous): int
    {
        if ($previous > 0) {
            return (int) round(($current - $previous) / $previous * 100);
        }

        return $current > 0 ? 100 : 0;
    }
}
