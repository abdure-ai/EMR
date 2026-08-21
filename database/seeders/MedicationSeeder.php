<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    protected array $medications = [
        ['name' => 'Black Seed Oil (Nigella Sativa)', 'form' => 'Oil', 'strength' => '100ml', 'price' => 250],
        ['name' => 'Honey Elixir', 'form' => 'Syrup', 'strength' => '250ml', 'price' => 180],
        ['name' => 'Fenugreek Powder', 'form' => 'Powder', 'strength' => '100g', 'price' => 120],
        ['name' => 'Ginger Capsules', 'form' => 'Capsule', 'strength' => '500mg', 'price' => 150],
        ['name' => 'Chamomile Tea', 'form' => 'Tea', 'strength' => '20 sachets', 'price' => 100],
        ['name' => 'Senna Tablets', 'form' => 'Tablet', 'strength' => '25mg', 'price' => 90],
        ['name' => 'Paracetamol', 'form' => 'Tablet', 'strength' => '500mg', 'price' => 30],
    ];

    public function run(): void
    {
        foreach ($this->medications as $data) {
            Medication::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
