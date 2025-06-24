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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->onDelete('cascade'); // Satu booking hanya bisa di-review sekali
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('babysitter_id')->constrained('babysitters')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // Rating 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
