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
        Schema::table('encounters', function (Blueprint $table) {
            $table->date('follow_up_date')->nullable();
            $table->string('follow_up_reason')->nullable();
            $table->dateTime('follow_up_dismissed_at')->nullable();

            $table->index('follow_up_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            $table->dropIndex(['follow_up_date']);
            $table->dropColumn(['follow_up_date', 'follow_up_reason', 'follow_up_dismissed_at']);
        });
    }
};
