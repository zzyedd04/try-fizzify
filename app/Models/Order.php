<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'code', 'user_id', 'product_id', 'quantity', 'unit_price', 'total',
        'customer_name', 'phone', 'address',
        'payment_status', 'fulfillment_status', 'payment_type', 'transaction_id',
        'snap_token', 'paid_at', 'counted',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'counted' => 'boolean',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** Pesanan yang sudah dibayar lewat Midtrans */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /** Isi keranjang user: pesanan yang sudah dibayar + pesanan menunggu bayar (token Snap berlaku 24 jam) */
    public function scopeInCart($query)
    {
        return $query->where(function ($q) {
            $q->where('payment_status', 'paid')
                ->orWhere(fn ($p) => $p->where('payment_status', 'pending')
                    ->whereNotNull('snap_token')
                    ->where('created_at', '>=', now()->subDay()));
        });
    }

    /** Pesanan yang dihitung sebagai omzet: sudah dibayar dan tidak dibatalkan admin */
    public function scopeRevenue($query)
    {
        return $query->where('payment_status', 'paid')->where('fulfillment_status', '!=', 'cancelled');
    }

    /** Avatar bulat 3D untuk daftar order (berdasarkan id supaya konsisten) */
    public function getAvatarUrlAttribute(): string
    {
        $avatars = ['avatar-1', 'avatar-2', 'avatar-3', 'avatar-4'];

        return asset('assets/img/'.$avatars[$this->id % count($avatars)].'.webp');
    }

    public function getFulfillmentLabelAttribute(): string
    {
        return match ($this->fulfillment_status) {
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            default => 'Ordered',
        };
    }
}
