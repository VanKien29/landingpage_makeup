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
        Schema::table('workshop_registrations', function (Blueprint $table) {
            $table->string('experience')->nullable();
            $table->boolean('attendance_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table) {
            $table->dropColumn(['experience', 'attendance_confirmed', 'confirmed_at']);
        });
    }
};
