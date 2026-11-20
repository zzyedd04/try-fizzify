<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;

/** Keranjang user: daftar pesanan milik user yang sedang login */
class CartController extends Controller
{
    public function index(Request $request, MidtransService $midtrans)
    {
        $user = $request->user();

        // Cek status pembayaran terbaru ke Midtrans, supaya pesanan yang baru dibayar langsung tampil "Dibayar"
        $midtrans->syncPending($user->id);

        return view('cart', [
            'orders' => $user->orders()->inCart()->with('product')->latest()->get(),
            'midtransReady' => $midtrans->isConfigured(),
            'midtransClientKey' => $midtrans->clientKey(),
            'snapJsUrl' => $midtrans->snapJsUrl(),
        ]);
    }
}
