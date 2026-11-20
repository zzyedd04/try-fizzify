<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun admin bawaan (ganti passwordnya sebelum dipakai sungguhan).
        // firstOrCreate: menjalankan seeder lagi TIDAK mereset password admin yang sudah diganti.
        $admin = User::firstOrCreate(['username' => 'admin'], [
            'name' => 'Admin Fizzify',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        if (! $admin->isAdmin()) {
            $admin->update(['role' => 'admin']);
        }

        $this->call(ProductSeeder::class);
    }
}
