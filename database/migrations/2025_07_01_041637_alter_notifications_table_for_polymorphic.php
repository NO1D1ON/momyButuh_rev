<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
                // Cek apakah ada foreign key constraint sebelum drop
                // Nama constraint biasanya: {table}_{column}_foreign
                $foreignKeys = collect(DB::select("SHOW CREATE TABLE notifications"))->first()->{"Create Table"};
                if (str_contains($foreignKeys, 'user_id')) {
                    $table->dropForeign('user_id');
                }
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