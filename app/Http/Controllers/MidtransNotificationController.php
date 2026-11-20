<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhook Midtrans. Daftarkan URL ini di Midtrans Dashboard:
 *   Settings -> Configuration -> Payment Notification URL  =>  https://domain-kamu/midtrans/notification
 * (Di localhost bisa pakai ngrok. Tanpa webhook pun status tetap bisa tersinkron lewat CheckoutController@sync.)
 */
class MidtransNotificationController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        if (! $midtrans->hasValidSignature($payload)) {
            return response()->json(['message' => 'Signature tidak valid'], 403);
        }

        $order = Order::where('code', $payload['order_id'] ?? '')->first();

        if (! $order) {
            // Midtrans mengirim notifikasi tes dengan order_id acak, cukup dijawab OK
            return response()->json(['message' => 'Order tidak ditemukan, diabaikan']);
        }

        $midtrans->applyStatus($order, $payload);

        return response()->json(['message' => 'OK']);
    }
}
