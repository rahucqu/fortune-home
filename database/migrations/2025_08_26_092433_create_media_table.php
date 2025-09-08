<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->string('type'); // image, document, video, audio, other
            $table->integer('size'); // file size in bytes
            $table->integer('width')->nullable(); // for images
            $table->integer('height')->nullable(); // for images
            $table->text('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // additional file metadata
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['mime_type', 'is_active']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
