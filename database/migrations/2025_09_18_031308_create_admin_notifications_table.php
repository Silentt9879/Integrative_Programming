<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->boolean('action_required')->default(false);
            $table->unsignedBigInteger('related_user_id')->nullable();
            $table->unsignedBigInteger('related_booking_id')->nullable();
            $table->json('metadata');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_notifications');
    }
};