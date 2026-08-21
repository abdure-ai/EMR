<?php

namespace Tests\Feature\Services;

use App\Livewire\Investigations\InvestigationCreate;
use App\Livewire\Investigations\InvestigationIndex;
use App\Models\Investigation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvestigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_clinic_manager_can_create_an_investigation(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(InvestigationCreate::class)
            ->set('category', 'lab')
            ->set('subcategory', 'Hematology')
            ->set('name', 'Complete Blood Count')
            ->set('price', '150')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('investigations', [
            'category' => 'lab', 'subcategory' => 'Hematology', 'name' => 'Complete Blood Count',
        ]);
    }

    public function test_investigation_creation_requires_a_valid_category(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(InvestigationCreate::class)
            ->set('category', 'not-a-real-category')
            ->set('name', 'CBC')
            ->call('save')
            ->assertHasErrors(['category']);
    }

    public function test_index_filters_by_category_and_search(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        Investigation::create(['category' => 'lab', 'subcategory' => 'Hematology', 'name' => 'CBC', 'price' => 150]);
        Investigation::create(['category' => 'imaging', 'subcategory' => 'X-Ray', 'name' => 'Chest X-Ray', 'price' => 300]);

        $this->actingAs($manager);

        Livewire::test(InvestigationIndex::class)
            ->set('category', 'lab')
            ->assertSee('CBC')
            ->assertDontSee('Chest X-Ray');
    }

    public function test_reception_cannot_manage_investigations(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('investigations.index'))
            ->assertForbidden();
    }
}
