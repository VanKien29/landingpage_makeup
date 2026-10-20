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
        Schema::create('workshop_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_event_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('phone', 40);
            $table->string('email');
            $table->string('social');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('consented_at');
            $table->string('consent_version')->default('2026-10-v1');
            $table->string('bank_reference', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->index(['workshop_event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_registrations');
    }
};
