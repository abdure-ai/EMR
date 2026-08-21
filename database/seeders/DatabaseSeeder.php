<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Not using WithoutModelEvents here: Patient relies on a creating() model
     * event to generate its NES-YYYY-NNNNNN id, so model events must stay on.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            PatientSeeder::class,
            DepartmentSeeder::class,
            ServiceSeeder::class,
            InvestigationSeeder::class,
            MedicationSeeder::class,
            PatientBulkDemoSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
