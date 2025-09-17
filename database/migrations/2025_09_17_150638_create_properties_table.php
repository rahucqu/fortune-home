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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->enum('listing_type', ['sale', 'rent'])->default('sale');
            $table->enum('status', ['available', 'sold', 'rented', 'pending', 'draft'])->default('available');

            // Price information
            $table->decimal('price', 15, 2);
            $table->decimal('monthly_rent', 15, 2)->nullable();
            $table->string('currency', 3)->default('BDT');

            // Property details
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('total_rooms')->nullable();
            $table->decimal('area_sqft', 10, 2)->nullable();
            $table->decimal('land_area_sqft', 10, 2)->nullable();
            $table->integer('floors')->nullable();
            $table->integer('floor_number')->nullable();
            $table->year('built_year')->nullable();

            // Address information
            $table->string('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('postal_code')->nullable();

            // Additional features
            $table->boolean('is_furnished')->default(false);
            $table->boolean('has_parking')->default(false);
            $table->integer('parking_spaces')->default(0);
            $table->boolean('pet_friendly')->default(false);
            $table->boolean('is_featured')->default(false);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Foreign keys
            $table->foreignId('property_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');

            // Additional fields
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('inquiries_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['status', 'listing_type']);
            $table->index(['property_type_id', 'status']);
            $table->index(['location_id', 'status']);
            $table->index(['agent_id', 'status']);
            $table->index(['price', 'listing_type']);
            $table->index(['bedrooms', 'bathrooms']);
            $table->index(['is_featured', 'status']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
