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
    $table->timestamp('read_at')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->string('status')->default('show');
    $table->string('type')->default('text')->after('content');
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
