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
        Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->string('type'); // private | group
    $table->string('name')->nullable();
    $table->string('avatar')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->string('status')->default('show');
    $table->string('createUser');
    $table->timestamp('deleted_at')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
