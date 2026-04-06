<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('message_media')) {
            Schema::create('message_media', function (Blueprint $table) {
                $table->id();

                // liên kết message
                $table->foreignId('message_id')
                      ->constrained()
                      ->cascadeOnDelete();

                // đường dẫn file
                $table->string('file_path');

                // loại file
                $table->string('type')->default('image'); // image, video, file

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_media');
    }
};
