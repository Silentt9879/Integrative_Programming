<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('session_id')->nullable()->after('user_agent');
            $table->json('event_data')->nullable()->after('metadata');
        });
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['user_agent', 'session_id', 'event_data']);
        });
    }
};