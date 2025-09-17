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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('license_number')->unique()->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('office_address')->nullable();
            $table->json('social_media')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->integer('properties_sold')->default(0);
            $table->integer('experience_years')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'name']);
            $table->index('license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
