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
        Schema::table('babysitters', function (Blueprint $table) {
            // Tambahkan kolom saldo setelah rate_per_hour
            $table->unsignedBigInteger('balance')->default(0)->after('rate_per_hour');
        });
    }

    public function down(): void
    {
        Schema::table('babysitters', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
