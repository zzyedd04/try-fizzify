<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MidtransService
{
    public function __construct(private SalesService $sales)
    {
    }

    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key'));
    }

    public function clientKey(): ?string
    {
        return config('services.midtrans.client_key');
    }

    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /** Opsi HTTP: verifikasi SSL bisa dimatikan HANYA untuk lokal lewat MIDTRANS_VERIFY_SSL=false */
    private function http()
    {
        return Http::withBasicAuth((string) config('services.midtrans.server_key'), '')
            ->withOptions(['verify' => (bool) config('services.midtrans.verify_ssl', true)])
            ->acceptJson();
    }

    private function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    private function snapEndpoint(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function apiBase(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /** Minta Snap token ke Midtrans untuk satu pesanan */
    public function createSnapToken(Order $order, Product $product, User $user): string
    {
        $customer = array_filter([
            'first_name' => Str::limit($order->customer_name, 50, ''),
            'email' => $user->email,
            'phone' => $order->phone,
            'shipping_address' => [
                'first_name' => Str::limit($order->customer_name, 50, ''),
                'phone' => $order->phone,
                'address' => Str::limit($order->address, 200, ''),
                'country_code' => 'IDN',
            ],
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->code,
                'gross_amount' => $order->total,
            ],
            'item_details' => [[
                'id' => (string) $product->id,
                'price' => $order->unit_price,
                'quantity' => $order->quantity,
                'name' => Str::limit('Fizzify '.$product->name, 50, ''),
            ]],
            'customer_details' => $customer,
        ];

        $response = $this->http()
            ->asJson()
            ->timeout(20)
            ->post($this->snapEndpoint(), $payload);

        if (! $response->successful() || ! $response->json('token')) {
            $message = $response->json('error_messages.0') ?? $response->json('status_message') ?? 'Midtrans menolak permintaan.';
            throw new RuntimeException($message);
        }

        return $response->json('token');
    }

    /** Tanya status transaksi langsung ke Midtrans (dipakai setelah popup Snap selesai) */
    public function fetchStatus(string $orderCode): ?array
    {
        $response = $this->http()
            ->timeout(10)
            ->get($this->apiBase().'/'.$orderCode.'/status');

        $json = $response->json();

        // Midtrans membalas HTTP 200 dengan status_code 404 bila transaksi belum dibuat
        if (! is_array($json) || ($json['status_code'] ?? null) === '404') {
            return null;
        }

        return $json;
    }

    /**
     * Sinkronkan pesanan yang masih "pending" dengan status asli di Midtrans.
     * Dipanggil saat admin membuka Dashboard/Orders/Products dan saat user membuka Keranjang,
     * jadi pembayaran yang selesai tetap tercatat walau webhook tidak bisa masuk ke localhost
     * atau popup pembayaran ditutup sebelum sempat sinkron.
     */
    public function syncPending(?int $userId = null, int $limit = 15): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $orders = Order::where('payment_status', 'pending')
            ->whereNotNull('snap_token')
            ->where('created_at', '>=', now()->subDays(2))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->limit($limit)
            ->get();

        foreach ($orders as $order) {
            try {
                $status = $this->fetchStatus($order->code);

                if ($status) {
                    $this->applyStatus($order, $status);
                }
            } catch (Throwable $e) {
                report($e);
                break; // Midtrans tidak terjangkau, jangan coba pesanan lain
            }
        }
    }

    /** Validasi signature_key pada notifikasi (webhook) Midtrans */
    public function hasValidSignature(array $payload): bool
    {
        $expected = hash('sha512',
            ($payload['order_id'] ?? '').
            ($payload['status_code'] ?? '').
            ($payload['gross_amount'] ?? '').
            config('services.midtrans.server_key')
        );

        return hash_equals($expected, (string) ($payload['signature_key'] ?? ''));
    }

    /** Terapkan status dari Midtrans (notifikasi atau hasil cek status) ke pesanan */
    public function applyStatus(Order $order, array $data): Order
    {
        // Jumlah yang dibayar harus sama dengan total pesanan
        if (isset($data['gross_amount']) && (int) round((float) $data['gross_amount']) !== $order->total) {
            return $order;
        }

        $status = $data['transaction_status'] ?? '';
        $fraud = $data['fraud_status'] ?? 'accept';

        $new = match (true) {
            $status === 'settlement', $status === 'capture' && $fraud === 'accept' => 'paid',
            $status === 'capture', $status === 'pending' => 'pending',
            in_array($status, ['deny', 'cancel', 'failure'], true) => 'failed',
            $status === 'expire' => 'expired',
            in_array($status, ['refund', 'partial_refund', 'chargeback'], true) => 'refunded',
            default => $order->payment_status,
        };

        return DB::transaction(function () use ($order, $data, $new) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Notifikasi lama (pending/failed) tidak boleh menurunkan pesanan yang sudah lunas
            if ($order->payment_status === 'paid' && in_array($new, ['pending', 'failed', 'expired'], true)) {
                return $order;
            }

            $order->payment_type = $data['payment_type'] ?? $order->payment_type;
            $order->transaction_id = $data['transaction_id'] ?? $order->transaction_id;
            $order->payment_status = $new;

            if ($new === 'paid') {
                $order->paid_at ??= now();
                $order->save();
                $this->sales->countSale($order);
            } elseif ($new === 'refunded') {
                $order->save();
                $this->sales->releaseSale($order);
            } else {
                $order->save();
            }

            return $order->fresh();
        });
    }
}
