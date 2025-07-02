<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // 1. Hapus tabel notifikasi yang lama jika ada (Ini adalah langkah kunci)
        Schema::dropIfExists('notifications');

        // 2. Buat kembali tabel notifikasi dengan struktur polymorphic yang benar
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // Membuat notifiable_type & notifiable_id
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
    }
};