<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    protected array $services = [
        ['department' => 'General Medicine', 'name' => 'Initial Consultation', 'duration_minutes' => 45, 'price' => 300],
        ['department' => 'General Medicine', 'name' => 'Follow-up Consultation', 'duration_minutes' => 20, 'price' => 150],
        ['department' => 'Herbal Therapy', 'name' => 'Herbal Therapy Session', 'duration_minutes' => 60, 'price' => 500],
        ['department' => 'Cupping Therapy (Hijama)', 'name' => 'Cupping Therapy (Hijama)', 'duration_minutes' => 40, 'price' => 400],
    ];

    public function run(): void
    {
        foreach ($this->services as $data) {
            $department = Department::where('name', $data['department'])->firstOrFail();

            Service::firstOrCreate(
                ['name' => $data['name']],
                ['department_id' => $department->id, 'duration_minutes' => $data['duration_minutes'], 'price' => $data['price']]
            );
        }
    }
}
