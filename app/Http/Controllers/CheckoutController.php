<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private MidtransService $midtrans)
    {
    }

    /** Buat pesanan (status pending) lalu minta Snap token ke Midtrans */
    public function store(Request $request): JsonResponse
    {
        if (! $this->midtrans->isConfigured()) {
            return response()->json([
                'message' => 'Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env.',
            ], 503);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'customer_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'address' => ['required', 'string', 'max:200'],
        ], [], [
            'customer_name' => 'Nama',
            'phone' => 'No. HP',
            'address' => 'Alamat',
        ]);

        $product = Product::active()->findOrFail($data['product_id']);

        if ($product->stock < $data['quantity']) {
            return response()->json(['message' => "Stok {$product->name} tinggal {$product->stock}."], 422);
        }

        $order = Order::create([
            'code' => 'FZ'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'unit_price' => $product->price,
            'total' => $product->price * $data['quantity'],
            'customer_name' => $data['customer_name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
        ]);

        try {
            $order->snap_token = $this->midtrans->createSnapToken($order, $product, $request->user());
            $order->save();
        } catch (Throwable $e) {
            report($e);
            $order->delete();

            return response()->json(['message' => 'Gagal menghubungi Midtrans: '.$e->getMessage()], 502);
        }

        return response()->json([
            'order_code' => $order->code,
            'snap_token' => $order->snap_token,
        ]);
    }

    /**
     * Dipanggil browser setelah popup Snap selesai. Status dicek langsung ke server Midtrans,
     * jadi tetap jalan di localhost tanpa webhook.
     */
    public function sync(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        try {
            $status = $this->midtrans->fetchStatus($order->code);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['payment_status' => $order->payment_status, 'message' => 'Gagal cek status ke Midtrans.'], 502);
        }

        if ($status) {
            $order = $this->midtrans->applyStatus($order, $status);
        }

        return response()->json([
            'payment_status' => $order->payment_status,
            'paid' => $order->payment_status === 'paid',
        ]);
    }
}
