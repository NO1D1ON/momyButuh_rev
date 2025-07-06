<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rename kolom 'job_date' menjadi 'start_date' jika kolom tersebut ada
        if (Schema::hasColumn('job_offers', 'job_date')) {
            // Gunakan raw SQL agar lebih aman untuk shared hosting (seperti CPanel)
            DB::statement("ALTER TABLE `job_offers` CHANGE `job_date` `start_date` DATE NULL DEFAULT NULL");
        }

        // Tambahkan kolom 'end_date' yang nullable untuk mencegah error
        if (!Schema::hasColumn('job_offers', 'end_date')) {
            Schema::table('job_offers', function (Blueprint $table) {
                $table->date('end_date')->nullable()->after('start_date');
            });
        }
    }

    public function down(): void
    {
        // Hapus kolom 'end_date' jika ada
        if (Schema::hasColumn('job_offers', 'end_date')) {
            Schema::table('job_offers', function (Blueprint $table) {
                $table->dropColumn('end_date');
            });
        }

        // Rename kembali 'start_date' ke 'job_date' jika ada
        if (Schema::hasColumn('job_offers', 'start_date')) {
            DB::statement("ALTER TABLE `job_offers` CHANGE `start_date` `job_date` DATE NULL DEFAULT NULL");
        }
    }
};
