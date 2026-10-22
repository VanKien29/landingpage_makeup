<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('workshop_events')
            ->where('slug', 'the-back')
            ->update(['event_date' => '2026-12-20', 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('workshop_events')
            ->where('slug', 'the-back')
            ->update(['event_date' => null, 'updated_at' => now()]);
    }
};
