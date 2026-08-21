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
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_entry_id')->unique()->constrained('queue_entries')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('practitioner_id')->constrained('users')->restrictOnDelete();
            $table->text('patient_note')->nullable();
            $table->text('investigations')->nullable();
            $table->text('medications')->nullable();
            $table->text('results')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
