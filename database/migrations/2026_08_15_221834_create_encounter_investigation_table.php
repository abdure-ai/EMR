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
        Schema::create('encounter_investigation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignId('investigation_id')->constrained('investigations')->restrictOnDelete();
            // Snapshot of the price at the moment it was ordered - the
            // catalog price can change later without rewriting history.
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['encounter_id', 'investigation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounter_investigation');
    }
};
