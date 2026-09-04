<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_headline', 'hero_subheadline', 'hero_image_path', 'hero_cta_label', 'hero_cta_url',
    'home_highlights_heading', 'home_highlights_body',
    'about_heading', 'about_body', 'about_image_path',
    'contact_address', 'contact_phone', 'contact_email', 'contact_hours', 'contact_map_url',
    'facebook_url', 'instagram_url', 'telegram_url',
    'footer_note',
])]
class SiteContent extends Model
{
    use Auditable;

    /**
     * Singleton row (id=1), same pattern as ClinicSetting::current() - the
     * public website's editable content, created on first access with the
     * column defaults from the migration if it doesn't exist yet.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
