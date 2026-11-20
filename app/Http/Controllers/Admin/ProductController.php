<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(MidtransService $midtrans)
    {
        $midtrans->syncPending();

        $products = Product::orderBy('sort')->get();

        // Omzet per produk dihitung dari pesanan yang benar-benar dibayar
        $revenue = Order::revenue()
            ->selectRaw('product_id, SUM(total) as revenue')
            ->groupBy('product_id')
            ->pluck('revenue', 'product_id');

        return view('admin.products', [
            'products' => $products,
            'revenue' => $revenue,
            'totalSold' => $products->sum('sold'),
            'totalStock' => $products->sum('stock'),
            'bestSeller' => $products->sortByDesc('sold')->first(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'price' => ['required', 'integer', 'min:1000', 'max:1000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product->update([
            'price' => $data['price'],
            'stock' => $data['stock'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', $product->name.' berhasil diperbarui.');
    }
}
