<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Memperbaiki tabel `users` yang sudah terlanjur ada tetapi bentuknya beda
     * (misalnya tabel bawaan Laravel yang tidak punya kolom `username`).
     * Kalau tabel sudah benar, migration ini tidak mengubah apa-apa.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable();
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user');
            }
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable();
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
        });

        // Isi username untuk baris lama yang masih kosong
        $used = DB::table('users')->whereNotNull('username')->where('username', '!=', '')->pluck('username')->all();

        foreach (DB::table('users')->whereNull('username')->orWhere('username', '')->get() as $row) {
            $base = Str::slug(Str::before((string) ($row->email ?? ''), '@'), '') ?: 'user'.$row->id;
            $name = $base;
            $i = 1;

            while (in_array($name, $used, true)) {
                $name = $base.(++$i);
            }

            $used[] = $name;
            DB::table('users')->where('id', $row->id)->update(['username' => $name]);
        }

        // Email dan password harus boleh kosong (daftar hanya dengan username / lewat Google)
        $columns = collect(Schema::getColumns('users'))->keyBy('name');

        foreach (['email', 'password'] as $column) {
            if ($columns->has($column) && ! $columns[$column]['nullable']) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->string($column)->nullable()->change();
                });
            }
        }

        // Username harus unik (dilewati kalau index uniknya sudah ada)
        $hasUnique = collect(Schema::getIndexes('users'))
            ->contains(fn ($index) => ($index['unique'] ?? false) && $index['columns'] === ['username']);

        if (! $hasUnique) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('username');
                });
            } catch (Throwable) {
                //
            }
        }
    }

    public function down(): void
    {
        //
    }
};
