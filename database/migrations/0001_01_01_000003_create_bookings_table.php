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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Customer fields (for both guest and authenticated users)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);
            
            // Booking reference
            $table->string('booking_number', 20)->unique();
            
            // Foreign keys - user_id is nullable for guest bookings
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            
            // Booking dates and times
            $table->datetime('pickup_datetime');
            $table->datetime('return_datetime');
            $table->datetime('actual_return_datetime')->nullable();
            
            // Locations
            $table->string('pickup_location')->default('Main Office');
            $table->string('return_location')->default('Main Office');
            
            // Amounts
            $table->decimal('total_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->decimal('damage_charges', 10, 2)->default(0);
            $table->decimal('late_fees', 10, 2)->default(0);
            
            // Status
            $table->enum('status', [
                'pending',
                'confirmed', 
                'active',
                'completed',
                'cancelled',
                'no_show'
            ])->default('pending');
            
            $table->enum('payment_status', [
                'pending',
                'partial',
                'paid',
                'refunded',
                'cancelled'
            ])->default('pending');
            
            // Additional fields
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('pickup_inspection')->nullable();
            $table->json('return_inspection')->nullable();
            
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['vehicle_id', 'status']);
            $table->index(['pickup_datetime', 'return_datetime']);
            $table->index('booking_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('customer_email'); // For guest booking searches
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};