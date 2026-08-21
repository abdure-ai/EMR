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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            // 'registration' = one-off new-patient card fee, charged at
            // registration, before any practitioner/service is chosen.
            // 'visit' = tied to a check-in; paying it is what creates the
            // queue entry, so practitioner_id/service_id are set for these.
            $table->enum('type', ['registration', 'visit'])->default('visit');
            // Nullable: only set for 'visit' invoices - carried forward so the
            // cashier's payment approval knows who to queue the patient for.
            $table->foreignId('practitioner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->enum('status', ['pending', 'paid', 'waived', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
