<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;
use Throwable;

class InstallFizzify extends Command
{
    protected $signature = 'fizzify:install
                            {--fresh : Hapus SEMUA tabel di database lalu buat ulang dari awal}
                            {--no-seed : Jangan isi akun admin dan produk}';

    protected $description = 'Siapkan database Fizzify (buat database MySQL, tabel, akun admin, dan produk)';

    /** Tabel dan kolom penting yang harus ada supaya website berjalan */
    private array $required = [
        'users' => ['name', 'username', 'email', 'password', 'role', 'google_id', 'avatar'],
        'products' => ['slug', 'name', 'price', 'stock', 'sold', 'is_active'],
        'orders' => ['code', 'user_id', 'product_id', 'payment_status', 'fulfillment_status', 'counted'],
        'withdrawals' => ['type', 'amount', 'status'],
        'contact_messages' => ['first_name', 'phone', 'email', 'message'],
    ];

    public function handle(): int
    {
        $connection = config('database.default');
        $this->info("Koneksi database: {$connection}");

        if ($connection === 'mysql' && ! $this->createMysqlDatabase()) {
            return self::FAILURE;
        }

        try {
            DB::purge();
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->error('Tidak bisa terhubung ke database: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if ($this->input->isInteractive() && ! $this->confirm('--fresh akan MENGHAPUS semua tabel dan datanya di database ini. Lanjutkan?')) {
                return self::FAILURE;
            }
            $this->call('migrate:fresh', ['--force' => true]);
        } else {
            $this->call('migrate', ['--force' => true]);
        }

        if (! $this->option('no-seed')) {
            $this->call('db:seed', ['--force' => true]);
        }

        return $this->verify() ? self::SUCCESS : self::FAILURE;
    }

    /** Buat database MySQL kalau belum ada (penyebab error "Unknown database 'fizzify'") */
    private function createMysqlDatabase(): bool
    {
        $cfg = config('database.connections.mysql');

        if (! extension_loaded('pdo_mysql')) {
            $this->error('Ekstensi PHP pdo_mysql belum aktif. Di Laragon: Menu > PHP > Extensions > centang pdo_mysql.');

            return false;
        }

        $database = str_replace('`', '', (string) $cfg['database']);
        $dsn = ! empty($cfg['unix_socket'])
            ? "mysql:unix_socket={$cfg['unix_socket']}"
            : "mysql:host={$cfg['host']};port={$cfg['port']}";

        try {
            $pdo = new PDO($dsn, $cfg['username'], (string) ($cfg['password'] ?? ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$cfg['charset']} COLLATE {$cfg['collation']}");
        } catch (PDOException $e) {
            $this->error('Gagal terhubung ke MySQL: '.$e->getMessage());
            $this->line('Pastikan MySQL sudah jalan (Laragon > Start All) dan DB_USERNAME / DB_PASSWORD di file .env benar.');

            return false;
        }

        $this->info("Database `{$database}` siap.");

        return true;
    }

    /** Cek semua tabel/kolom penting dan tampilkan hasilnya */
    private function verify(): bool
    {
        $ok = true;
        $this->newLine();

        foreach ($this->required as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $this->error("Tabel `{$table}` TIDAK ADA.");
                $ok = false;
                continue;
            }

            $missing = array_values(array_filter($columns, fn ($c) => ! Schema::hasColumn($table, $c)));

            if ($missing) {
                $this->error("Tabel `{$table}` kurang kolom: ".implode(', ', $missing));
                $ok = false;
            } else {
                $this->line("<info>OK</info>  {$table}");
            }
        }

        if (! $ok) {
            $this->newLine();
            $this->warn('Ada tabel yang tidak sesuai. Kalau database ini masih kosong / tidak ada data penting, jalankan:');
            $this->line('  php artisan fizzify:install --fresh');

            return false;
        }

        $this->newLine();
        $this->info('Selesai! Login admin -> username: admin | password: admin123 (segera ganti).');

        return true;
    }
}
