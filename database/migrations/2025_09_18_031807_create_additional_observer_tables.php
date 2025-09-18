<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics_by_category', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric', 100);
            $table->string('category', 100);
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('customer_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('type', 50);
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('subject_type', 50);
            $table->unsignedBigInteger('subject_id');
            $table->string('action', 100);
            $table->string('legal_basis', 100)->nullable();
            $table->json('data_categories')->nullable();
            $table->string('timestamp', 50);
            $table->string('regulation', 50)->default('GDPR');
            $table->timestamps();
        });

        Schema::create('analytics_hourly', function (Blueprint $table) {
            $table->id();
            $table->datetime('datetime');
            $table->string('metric', 100);
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_by_category');
        Schema::dropIfExists('customer_stats');
        Schema::dropIfExists('compliance_logs');
        Schema::dropIfExists('analytics_hourly');
    }
};