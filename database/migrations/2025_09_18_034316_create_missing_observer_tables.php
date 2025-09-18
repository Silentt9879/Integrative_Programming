<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Customer email activities
        Schema::create('customer_email_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('activity_type', 100);
            $table->string('email_template', 100)->nullable();
            $table->json('metadata');
            $table->timestamps(); // Fix: Use Laravel's standard timestamps
        });

        // Security logs
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type', 50);
            $table->unsignedBigInteger('customer_id');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('login_method', 50)->nullable();
            $table->string('session_id')->nullable();
            $table->boolean('remember_me')->default(false);
            $table->boolean('is_suspicious')->default(false);
            $table->string('location_estimate')->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->string('previous_login')->nullable();
            $table->string('login_timestamp', 50);
            $table->json('metadata');
            $table->timestamps(); // Fix: Use Laravel's standard timestamps
        });

        // Business operation logs
        Schema::create('business_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type', 50);
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->string('changed_by_type', 20)->nullable();
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->text('change_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('financial_impact', 10, 2)->default(0);
            $table->string('customer_impact', 20)->nullable();
            $table->boolean('requires_followup')->default(false);
            $table->string('change_timestamp', 50);
            $table->json('metadata');
            $table->timestamps(); // Fix: Use Laravel's standard timestamps
        });

        // Admin activity logs
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type', 50);
            $table->unsignedBigInteger('admin_id');
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('report_type', 100)->nullable();
            $table->json('report_parameters')->nullable();
            $table->string('generation_status', 20)->nullable();
            $table->float('execution_time')->nullable();
            $table->json('data_accessed')->nullable();
            $table->string('export_format', 20)->nullable();
            $table->boolean('file_generated')->default(false);
            $table->string('ip_address', 45);
            $table->string('session_id')->nullable();
            $table->string('generation_timestamp', 50);
            $table->json('metadata');
            $table->timestamps(); // Fix: Use Laravel's standard timestamps
        });

        // Customer management logs
        Schema::create('customer_management_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type', 50);
            $table->unsignedBigInteger('customer_id');
            $table->string('customer_email');
            $table->string('customer_name');
            $table->string('registration_source', 50)->nullable();
            $table->string('registration_ip', 45)->nullable();
            $table->string('profile_completeness', 20)->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->boolean('data_protection_consent')->default(false);
            $table->boolean('marketing_consent')->default(false);
            $table->json('metadata');
            $table->timestamps(); // Fix: Use Laravel's standard timestamps
        });

        // Additional tables referenced by observers
        Schema::create('booking_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('status', 50);
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamp('updated_at')->useCurrent(); // Fix: Add default
        });

        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('session_start')->useCurrent(); // Fix: Add default
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->timestamps(); // Fix: Use Laravel's standard timestamps
        });

        Schema::create('customer_ltv', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('total_value', 10, 2)->default(0);
            $table->unsignedInteger('completed_bookings')->default(0);
            $table->timestamp('updated_at')->useCurrent(); // Fix: Add default
        });

        Schema::create('analytics_revenue', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->unsignedInteger('completed_bookings')->default(0);
            $table->timestamp('updated_at')->useCurrent(); // Fix: Add default
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_email_activities');
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('business_operation_logs');
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('customer_management_logs');
        Schema::dropIfExists('booking_stats');
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('customer_ltv');
        Schema::dropIfExists('analytics_revenue');
    }
};