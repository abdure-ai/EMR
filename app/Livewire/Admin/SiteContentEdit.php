<?php

namespace App\Livewire\Admin;

use App\Models\SiteContent;
use Livewire\Component;
use Livewire\WithFileUploads;

class SiteContentEdit extends Component
{
    use WithFileUploads;

    public string $hero_headline = '';

    public string $hero_subheadline = '';

    public string $hero_cta_label = '';

    public string $hero_cta_url = '';

    public $hero_image; // new upload, if any

    public string $home_highlights_heading = '';

    public string $home_highlights_body = '';

    public string $about_heading = '';

    public string $about_body = '';

    public $about_image; // new upload, if any

    public string $contact_address = '';

    public string $contact_phone = '';

    public string $contact_email = '';

    public string $contact_hours = '';

    public string $contact_map_url = '';

    public string $facebook_url = '';

    public string $instagram_url = '';

    public string $telegram_url = '';

    public string $footer_note = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        $content = SiteContent::current();

        foreach ($content->getFillable() as $field) {
            if (in_array($field, ['hero_image_path', 'about_image_path'])) {
                continue;
            }

            $this->{$field} = (string) ($content->{$field} ?? '');
        }
    }

    public function removeHeroImage(): void
    {
        $content = SiteContent::current();
        $content->update(['hero_image_path' => null]);
        session()->flash('status', 'Hero image removed.');
    }

    public function removeAboutImage(): void
    {
        $content = SiteContent::current();
        $content->update(['about_image_path' => null]);
        session()->flash('status', 'About image removed.');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        $validated = $this->validate([
            'hero_headline' => ['required', 'string', 'max:255'],
            'hero_subheadline' => ['nullable', 'string', 'max:1000'],
            'hero_cta_label' => ['nullable', 'string', 'max:60'],
            'hero_cta_url' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'home_highlights_heading' => ['nullable', 'string', 'max:255'],
            'home_highlights_body' => ['nullable', 'string', 'max:2000'],
            'about_heading' => ['nullable', 'string', 'max:255'],
            'about_body' => ['nullable', 'string', 'max:8000'],
            'about_image' => ['nullable', 'image', 'max:4096'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_hours' => ['nullable', 'string', 'max:500'],
            'contact_map_url' => ['nullable', 'url', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:255'],
            'footer_note' => ['nullable', 'string', 'max:500'],
        ]);

        unset($validated['hero_image'], $validated['about_image']);

        $content = SiteContent::current();

        if ($this->hero_image) {
            $validated['hero_image_path'] = $this->hero_image->store('site', 'public');
        }

        if ($this->about_image) {
            $validated['about_image_path'] = $this->about_image->store('site', 'public');
        }

        $content->update($validated);

        $this->reset(['hero_image', 'about_image']);

        session()->flash('status', 'Website content updated.');
    }

    public function render()
    {
        return view('livewire.admin.site-content-edit', [
            'content' => SiteContent::current(),
        ])->extends('layouts.app')->title('Website Content');
    }
}
