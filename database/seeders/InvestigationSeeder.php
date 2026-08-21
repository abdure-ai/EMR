<?php

namespace Database\Seeders;

use App\Models\Investigation;
use Illuminate\Database\Seeder;

class InvestigationSeeder extends Seeder
{
    protected array $investigations = [
        ['category' => 'lab', 'subcategory' => 'Hematology', 'name' => 'Complete Blood Count (CBC)', 'price' => 150],
        ['category' => 'lab', 'subcategory' => 'Biochemistry', 'name' => 'Liver Function Test', 'price' => 250],
        ['category' => 'lab', 'subcategory' => 'Biochemistry', 'name' => 'Renal Function Test', 'price' => 250],
        ['category' => 'lab', 'subcategory' => 'Serology', 'name' => 'Malaria RDT', 'price' => 100],
        ['category' => 'imaging', 'subcategory' => 'X-Ray', 'name' => 'Chest X-Ray', 'price' => 300],
        ['category' => 'imaging', 'subcategory' => 'Ultrasound', 'name' => 'Abdominal Ultrasound', 'price' => 500],
        ['category' => 'procedure', 'subcategory' => 'Minor Procedure', 'name' => 'Wound Dressing', 'price' => 100],
        ['category' => 'procedure', 'subcategory' => 'Minor Procedure', 'name' => 'Suturing', 'price' => 200],
    ];

    public function run(): void
    {
        foreach ($this->investigations as $data) {
            Investigation::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
