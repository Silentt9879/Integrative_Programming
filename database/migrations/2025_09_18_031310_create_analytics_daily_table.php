<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric', 100);
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_daily');
    }
};