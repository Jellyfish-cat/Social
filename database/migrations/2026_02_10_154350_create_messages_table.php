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

    $table->foreignId('conversation_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('sender_id')->nullable()
          ->constrained('users')
          ->cascadeOnDelete();

    $table->text('content')->nullable();
    $table->string('type')->default('text');
    $table->timestamp('read_at')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->nullable();
    $table->string('status')->default('show');
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
