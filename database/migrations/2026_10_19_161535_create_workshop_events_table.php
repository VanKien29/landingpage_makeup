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
        Schema::create('workshop_events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('tagline');
            $table->text('introduction');
            $table->date('event_date')->nullable();
            $table->string('venue')->default('Tokyo, Japan — Địa chỉ cụ thể sẽ được cập nhật.');
            $table->unsignedInteger('ticket_price')->default(5000);
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->unsignedSmallInteger('confirmed_tickets')->default(0);
            $table->boolean('registration_open')->default(true);
            $table->string('hero_image');
            $table->string('glam_image');
            $table->string('bridal_image');
            $table->text('payment_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_events');
    }
};
