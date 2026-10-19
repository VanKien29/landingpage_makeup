<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('course_name')->default('Khoá Makeup Chuyên Nghiệp');
            $table->string('batch_code');
            $table->string('start_date');
            $table->string('schedule_time');
            $table->string('location')->default('Tokyo, Japan');
            $table->integer('slots_total')->default(3);
            $table->integer('slots_available')->default(1);
            $table->string('status')->default('opening'); // opening, almost_full, full
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
