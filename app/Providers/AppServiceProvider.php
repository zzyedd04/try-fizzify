<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Muat helper rupiah() langsung, supaya tetap jalan walau autoload Composer belum di-regenerate
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        // Aman untuk MySQL/MariaDB versi lama di Laragon (batas panjang index utf8mb4)
        Schema::defaultStringLength(191);
    }
}
