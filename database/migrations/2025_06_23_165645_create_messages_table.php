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
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
        $table->unsignedBigInteger('sender_id'); // ID pengirim
        $table->string('sender_type'); // Tipe pengirim: 'App\Models\User' atau 'App\Models\Babysitter'
        $table->text('body');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
