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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate', 20)->unique();
            $table->string('make', 50);
            $table->string('model', 50);
            $table->integer('year');
            $table->string('color', 30);
            $table->enum('type', ['Sedan', 'SUV', 'Luxury', 'Economy', 'Truck', 'Van']);
            $table->integer('seating_capacity');
            $table->enum('fuel_type', ['Petrol', 'Diesel', 'Electric', 'Hybrid']);
            $table->decimal('current_mileage', 10, 2);
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['status', 'type']);
            $table->index(['make', 'model']);
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};