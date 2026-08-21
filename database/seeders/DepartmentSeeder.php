<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    protected array $departments = [
        'General Medicine',
        'Herbal Therapy',
        'Cupping Therapy (Hijama)',
    ];

    public function run(): void
    {
        foreach ($this->departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
