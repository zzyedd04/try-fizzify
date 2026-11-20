<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\MidtransService;
use Database\Seeders\ProductSeeder;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function about()
    {
        return view('about');
    }

    public function product(MidtransService $midtrans)
    {
        // Belum pernah di-seed? Isi 6 produk awal supaya halaman Product tidak kosong.
        if (Product::count() === 0) {
            app(ProductSeeder::class)->run();
        }

        return view('product', [
            'products' => Product::active()->orderBy('sort')->get(),
            'midtransReady' => $midtrans->isConfigured(),
            'midtransClientKey' => $midtrans->clientKey(),
            'snapJsUrl' => $midtrans->snapJsUrl(),
        ]);
    }

    public function portfolio()
    {
        return view('portfolio');
    }

    public function contact()
    {
        return view('contact');
    }
}
