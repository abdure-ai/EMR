<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\ClinicSettingsEdit;
use App\Models\ClinicSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClinicSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_clinic_manager_can_update_billing_settings(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(ClinicSettingsEdit::class)
            ->set('new_patient_card_fee', '150')
            ->set('revisit_free_within_days', '45')
            ->set('expiry_alert_days', '60')
            ->call('save')
            ->assertHasNoErrors();

        $settings = ClinicSetting::current();
        $this->assertEquals(150, $settings->new_patient_card_fee);
        $this->assertSame(45, $settings->revisit_free_within_days);
        $this->assertSame(60, $settings->expiry_alert_days);
    }

    public function test_settings_update_fails_validation_with_negative_fee(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(ClinicSettingsEdit::class)
            ->set('new_patient_card_fee', '-10')
            ->call('save')
            ->assertHasErrors(['new_patient_card_fee']);
    }

    public function test_reception_cannot_access_settings(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('settings.index'))
            ->assertForbidden();
    }
}
