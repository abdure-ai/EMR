<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    /**
     * Real starter copy for the public website, not test/demo data - safe to
     * run in production. Editable afterward from Admin > Website > Site
     * Content. Only fills in fields that are still empty, so re-running this
     * never clobbers anything a manager has already edited.
     */
    public function run(): void
    {
        $content = SiteContent::current();

        $defaults = [
            'hero_headline' => 'Trusted Islamic Herbal Care',
            'hero_subheadline' => 'Nesiha Herbal Clinic blends traditional Islamic herbal remedies with attentive, modern clinical care - guided by certified practitioners who treat every patient as family.',
            'hero_cta_label' => 'Book an Appointment',
            'hero_cta_url' => '/contact',
            'home_highlights_heading' => 'Why Families Trust Nesiha',
            'home_highlights_body' => 'From your first consultation to your treatment plan, every step is guided by practitioners who combine herbal tradition with careful, personal attention.',
            'about_heading' => 'Our Story',
            'about_body' => "Nesiha Herbal Clinic was founded to bring authentic, Islamic-compliant herbal treatment to families seeking a gentler path to wellness.\n\nOur practitioners are trained in traditional herbal medicine and modern clinical practice, so every remedy is both time-honored and carefully suited to each patient's needs.\n\nToday, Nesiha serves patients across the community with consultations, herbal therapy, and follow-up care rooted in compassion and respect.",
            'contact_address' => 'Bole Road, Addis Ababa, Ethiopia',
            'contact_phone' => '+251 91 234 5678',
            'contact_email' => 'info@endrismedicalcenter.com',
            'contact_hours' => "Sat - Thu: 8:00 AM - 6:00 PM\nFriday: Closed",
            'footer_note' => 'Trusted Islamic herbal care, rooted in tradition and delivered with modern clinical care.',
        ];

        foreach ($defaults as $key => $value) {
            if (empty($content->{$key})) {
                $content->{$key} = $value;
            }
        }

        $content->save();

        $descriptions = [
            'Initial Consultation' => 'A thorough first visit where our practitioners review your health history and symptoms to build a personalized herbal treatment plan.',
            'Follow-up Consultation' => 'A check-in visit to track your progress and adjust your treatment plan as you continue your healing journey.',
            'Herbal Therapy Session' => 'Guided herbal treatment sessions using traditional remedies, tailored to your condition and health goals.',
            'Cupping Therapy (Hijama)' => 'Traditional Islamic cupping therapy (Hijama), performed by trained practitioners to support circulation and natural healing.',
        ];

        foreach ($descriptions as $name => $description) {
            Service::where('name', $name)->whereNull('description')->update([
                'description' => $description,
                'show_on_website' => true,
            ]);
        }
    }
}
