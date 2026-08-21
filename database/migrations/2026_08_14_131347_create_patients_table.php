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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id', 16)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('sex', ['male', 'female']);
            // Nullable: many Ethiopian patients don't have a documented exact
            // birthdate. 'age' is what's actually captured at registration;
            // date_of_birth stays available for when it's genuinely known.
            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('phone');
            // Structured address (region/zone/woreda from the Ethiopian admin
            // boundary dataset, kebele/house_no as free text since kebele-level
            // data isn't available in that source).
            $table->string('region')->nullable();
            $table->string('zone')->nullable();
            $table->string('woreda')->nullable();
            $table->string('kebele')->nullable();
            $table->string('house_no')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->enum('preferred_language', ['ar', 'am', 'om', 'en'])->default('en');
            $table->string('photo_url')->nullable();
            $table->string('id_document_ref')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // Soft delete only: patient records are never hard-deleted from the
            // UI (retention/audit expectations for clinical data) - "Delete"
            // in the product is really "archive".
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
