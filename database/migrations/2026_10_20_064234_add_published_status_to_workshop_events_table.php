<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshop_events', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('registration_open');
        });
        DB::table('workshop_events')->where('slug', 'the-back')->update(['venue' => '〒135-0033東京都江東区深川2-2-3-1F']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_events', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
