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
        Schema::table('notifications', function (Blueprint $table) {
            // Hapus foreign key dan kolom user_id yang lama
            // Nama constraint bisa berbeda, sesuaikan jika perlu
            // Cek dulu apakah kolomnya ada sebelum drop
            if (Schema::hasColumn('notifications', 'user_id')) {
                // Hapus foreign key menggunakan perintah SQL langsung
                DB::statement('ALTER TABLE notifications DROP FOREIGN KEY notifications_user_id_foreign');

                // Hapus kolomnya menggunakan Schema Builder seperti biasa
                $table->dropColumn('user_id');
            }

            // Tambahkan kolom polimorfik yang dibutuhkan Laravel
            // 'morphs' akan membuat kolom notifiable_id (BIGINT) dan notifiable_type (VARCHAR)
            $table->morphs('notifiable');
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