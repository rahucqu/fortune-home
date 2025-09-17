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
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_url')->nullable(); // For external URLs
            $table->string('title')->nullable();
            $table->text('alt_text')->nullable();
            $table->string('type')->default('gallery'); // gallery, floor_plan, exterior, interior
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Store image dimensions, size, etc.
            $table->timestamps();

            $table->index(['property_id', 'type']);
            $table->index(['property_id', 'is_primary']);
            $table->index(['property_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_images');
    }
};
