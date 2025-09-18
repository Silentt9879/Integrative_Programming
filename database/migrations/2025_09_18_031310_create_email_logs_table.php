<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email');
            $table->string('template', 100);
            $table->string('subject');
            $table->text('content');
            $table->json('data');
            $table->enum('status', ['sent', 'failed']);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent(); // Fix: Add default current timestamp
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};