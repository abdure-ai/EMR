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
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();

            $table->string('hero_headline')->default('Trusted Islamic Herbal Care');
            $table->text('hero_subheadline')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_cta_label')->nullable();
            $table->string('hero_cta_url')->nullable();

            $table->string('home_highlights_heading')->nullable();
            $table->text('home_highlights_body')->nullable();

            $table->string('about_heading')->nullable();
            $table->longText('about_body')->nullable();
            $table->string('about_image_path')->nullable();

            $table->string('contact_address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('contact_hours')->nullable();
            $table->string('contact_map_url')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('telegram_url')->nullable();

            $table->text('footer_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
