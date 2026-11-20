<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat yang boleh mengubah angka "terjual" dan stok.
 * Angka terjual hanya bertambah ketika pembayaran Midtrans berhasil (status paid),
 * dan hanya satu kali per pesanan (ditandai kolom orders.counted).
 */
class SalesService
{
    public function countSale(Order $order): void
    {
        if ($order->counted) {
            return;
        }

        DB::transaction(function () use ($order) {
            $product = Product::whereKey($order->product_id)->lockForUpdate()->first();

            if ($product) {
                $product->sold += $order->quantity;
                $product->stock = max(0, $product->stock - $order->quantity);
                $product->save();
            }

            $order->counted = true;
            $order->save();
        });
    }

    /** Dipakai saat admin membatalkan pesanan atau Midtrans mengirim status refund */
    public function releaseSale(Order $order): void
    {
        if (! $order->counted) {
            return;
        }

        DB::transaction(function () use ($order) {
            $product = Product::whereKey($order->product_id)->lockForUpdate()->first();

            if ($product) {
                $product->sold = max(0, $product->sold - $order->quantity);
                $product->stock += $order->quantity;
                $product->save();
            }

            $order->counted = false;
            $order->save();
        });
    }
}
