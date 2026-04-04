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
        Schema::table('reports', function (Blueprint $table) {
            $table->string('category')->nullable()->after('target_type');
            $table->text('admin_note')->nullable()->after('status');
            $table->unsignedBigInteger('resolved_by')->nullable()->after('admin_note');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['category', 'admin_note', 'resolved_by', 'resolved_at']);
        });
    }
};
