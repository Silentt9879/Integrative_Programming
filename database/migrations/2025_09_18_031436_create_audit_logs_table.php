<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 100);
            $table->string('event_type', 100);
            $table->string('event_category', 50);
            $table->string('timestamp', 50);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('ip_address', 45);
            $table->json('metadata');
            $table->enum('severity', ['info', 'warning', 'error']);
            $table->boolean('compliance_flag')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};