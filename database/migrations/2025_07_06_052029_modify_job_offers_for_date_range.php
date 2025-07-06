<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            // Rename kolom 'job_date' menjadi 'start_date' jika kolom tersebut ada
            if (Schema::hasColumn('job_offers', 'job_date')) {
                $table->renameColumn('job_date', 'start_date');
            }

            // Tambahkan kolom 'end_date' setelah 'start_date'
            $table->date('end_date')->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            // Drop kolom 'end_date' jika ada
            if (Schema::hasColumn('job_offers', 'end_date')) {
                $table->dropColumn('end_date');
            }

            // Rename kolom 'start_date' kembali menjadi 'job_date' jika ada
            if (Schema::hasColumn('job_offers', 'start_date')) {
                $table->renameColumn('start_date', 'job_date');
            }
        });
    }
};
