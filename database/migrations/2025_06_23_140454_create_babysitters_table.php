<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('babysitters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->date('birth_date');
            $table->text('address')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedInteger('rate_per_hour')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('babysitters');
    }
};