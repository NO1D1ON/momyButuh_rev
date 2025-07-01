<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('babysitter_availabilities', function (Blueprint $table) {
            // TAMBAHKAN DUA KOLOM INI
            // Tipe data decimal/double cocok untuk koordinat.
            // `nullable()` membuatnya tidak wajib diisi (aman untuk data lama).
            // `after('notes')` menempatkan kolom ini setelah kolom 'notes'.
            $table->decimal('latitude', 10, 8)->nullable()->after('notes');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('babysitter_availabilities', function (Blueprint $table) {
            // Ini untuk membatalkan migrasi jika diperlukan
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};