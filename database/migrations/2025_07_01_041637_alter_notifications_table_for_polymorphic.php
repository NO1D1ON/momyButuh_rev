<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Pastikan DB di-import

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     * Mengubah tabel notifikasi agar kompatibel dengan sistem Notifiable Laravel.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // [PERBAIKAN #1] Hapus kolom user_id dengan cara yang lebih aman.
            // Metode ini akan secara otomatis menghapus foreign key yang terkait
            // dengan kolom 'user_id' tanpa perlu tahu nama pastinya.
            if (Schema::hasColumn('notifications', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            // [PERBAIKAN #2] Tambahkan kolom polimorfik dan UUID.
            // Ini adalah struktur standar untuk notifikasi Laravel.
            $table->uuid('id')->change(); // Ubah primary key menjadi UUID
            $table->string('type');
            $table->morphs('notifiable'); // Membuat notifiable_type dan notifiable_id
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            
            // Hapus kolom lama yang tidak lagi diperlukan
            if (Schema::hasColumn('notifications', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('notifications', 'message')) {
                $table->dropColumn('message');
            }
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Logika untuk mengembalikan ke kondisi semula jika diperlukan
            if (Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->dropMorphs('notifiable');
            }
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }
            
            // Kembalikan kolom user_id
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
        });
    }
};
