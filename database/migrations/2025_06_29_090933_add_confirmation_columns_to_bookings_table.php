<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Kolom untuk menyimpan waktu konfirmasi dari Orang Tua
            $table->timestamp('parent_confirmed_at')->nullable()->after('status');
            // Kolom untuk menyimpan waktu konfirmasi dari Babysitter
            $table->timestamp('babysitter_confirmed_at')->nullable()->after('parent_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['parent_confirmed_at', 'babysitter_confirmed_at']);
        });
    }
};