<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dulu tabel ini ikut di migration withdrawals, sehingga tidak terbuat kalau migration
     * withdrawals sudah pernah dijalankan. Sekarang dipisah dan aman dijalankan ulang.
     */
    public function up(): void
    {
        if (Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone', 30);
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
