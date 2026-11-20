<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\SalesService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, MidtransService $midtrans)
    {
        $midtrans->syncPending();

        $range = $request->query('range') === 'all' ? 'all' : 'today';

        $orders = Order::paid()
            ->with(['product', 'user'])
            ->when($range === 'today', fn ($q) => $q->whereDate('paid_at', today()))
            ->latest('paid_at')
            ->get();

        return view('admin.orders', [
            'orders' => $orders,
            'range' => $range,
            'todayCount' => Order::paid()->whereDate('paid_at', today())->count(),
            'allCount' => Order::paid()->count(),
        ]);
    }

    public function confirm(Order $order)
    {
        abort_unless($order->payment_status === 'paid', 422);

        $order->update(['fulfillment_status' => 'confirmed']);

        return back()->with('success', "Pesanan {$order->code} dikonfirmasi.");
    }

    public function cancel(Order $order, SalesService $sales)
    {
        abort_unless($order->payment_status === 'paid', 422);

        $order->update(['fulfillment_status' => 'cancelled']);
        // Pesanan batal tidak dihitung sebagai terjual / omzet
        $sales->releaseSale($order);

        return back()->with('success', "Pesanan {$order->code} dibatalkan. Refund dilakukan manual di dashboard Midtrans.");
    }
}
