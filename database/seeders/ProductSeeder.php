<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Semua produk mulai dari terjual 0. Angkanya naik otomatis HANYA saat ada pembayaran
        // Midtrans yang berhasil. firstOrCreate: menjalankan seeder lagi tidak mereset angka terjual.
        $products = [
            ['strawberry-blast', 'Strawberry Blast', 'Manis dan segar dengan rasa stroberi yang menggoda', 'can-strawberry.webp', '#EF5B62', 0],
            ['lemon-splash', 'Lemon Splash', 'Sensasi lemon segar dengan sentuhan asam-manis', 'can-lemon.webp', '#C9A21B', 0],
            ['blueberry-pop', 'Blueberry Pop', 'Rasa blueberry manis dengan sensasi soda bergelembung', 'can-blueberry.webp', '#17598F', 0],
            ['green-apple', 'Green Apple', 'Perpaduan apel hijau yang renyah dan menyegarkan', 'can-green.webp', '#1E5A28', 0],
            ['grape-berry', 'Grape Berry', 'Rasa anggur manis dengan sensasi soda yang seru', 'can-grape.webp', '#5A2A6B', 0],
            ['orange-burst', 'Orange Burst', 'Rasa jeruk ceria dengan kesegaran citrus yang nikmat', 'can-orange.webp', '#D2601A', 0],
        ];

        foreach ($products as $i => [$slug, $name, $desc, $image, $color, $sold]) {
            Product::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $desc,
                'price' => 12000,
                'image' => $image,
                'color' => $color,
                'stock' => 1000,
                'sold' => $sold,
                'is_active' => true,
                'sort' => $i + 1,
            ]);
        }
    }
}
