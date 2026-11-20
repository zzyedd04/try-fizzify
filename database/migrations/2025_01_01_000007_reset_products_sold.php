<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Angka "terjual" sebelumnya berisi angka contoh (410, 848, dst.). Di sini dihitung ulang dari
     * pesanan yang benar-benar sudah dibayar lewat Midtrans, jadi produk yang belum pernah dibeli = 0.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('orders')) {
            return;
        }

        $counted = DB::table('orders')
            ->where('counted', true)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        foreach (DB::table('products')->pluck('id') as $id) {
            DB::table('products')->where('id', $id)->update(['sold' => (int) ($counted[$id] ?? 0)]);
        }
    }

    public function down(): void
    {
        //
    }
};
