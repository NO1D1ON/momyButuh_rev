<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_approval_columns_to_bookings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColumnsToBookingsTable extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambahkan setelah kolom 'status' atau di mana pun yang sesuai
            $table->boolean('parent_approved')->default(false)->after('status');
            $table->boolean('babysitter_approved')->default(false)->after('parent_approved');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['parent_approved', 'babysitter_approved']);
        });
    }
}
