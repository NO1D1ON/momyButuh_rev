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
        Schema::create('babysitters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password'); // <-- Termasuk password
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_number')->nullable();
            $table->date('birth_date');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();   // <-- Termasuk latitude
            $table->decimal('longitude', 10, 7)->nullable();  // <-- Termasuk longitude
            $table->text('bio')->nullable();
            $table->unsignedInteger('rate_per_hour')->default(0);
            $table->decimal('rating', 2, 1)->default(0.0);    // <-- Termasuk rating
            $table->unsignedTinyInteger('experience_years')->default(0); // <-- Termasuk experience
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('babysitters');
    }
};