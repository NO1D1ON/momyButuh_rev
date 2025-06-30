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
        Schema::create('babysitter_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('babysitter_id')->constrained('babysitters')->onDelete('cascade');
            $table->date('available_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('rate_per_hour'); // Tarif yang ditentukan babysitter
            $table->string('location_preference'); // Lokasi preferensi kerja
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('babysitter_availabilities');
    }
};
