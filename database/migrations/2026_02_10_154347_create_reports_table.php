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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('target_id');
            $table->string('target_type');
            $table->string('category')->nullable(); 

            $table->text('reason')->nullable(); 
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable(); 
            $table->unsignedBigInteger('resolved_by')->nullable(); 
            $table->timestamp('resolved_at')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
