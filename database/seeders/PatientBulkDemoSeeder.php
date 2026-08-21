<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientMedicalInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientBulkDemoSeeder extends Seeder
{
    protected array $complaints = [
        'Chronic joint pain', 'Digestive discomfort', 'Fatigue and low energy',
        'Skin irritation', 'Sleep difficulty', 'High blood pressure follow-up',
        'Headache and dizziness', 'Lower back pain', 'Seasonal allergies',
        'Cough and congestion', 'Stress and anxiety', 'Post-injury recovery',
        'Menstrual irregularities', 'Weight management', 'General wellness check-up',
    ];

    protected array $allergies = [
        'None known', 'Penicillin', 'Dust', 'Sulfa drugs', 'Pollen', null, null,
    ];

    /**
     * ~50 realistic demo patients spread across today and the past 30 days,
     * so the today/all-time scope toggle and pagination have real volume to show.
     */
    public function run(): void
    {
        $reception = User::where('email', 'reception@nesiha.test')->first();

        for ($i = 0; $i < 50; $i++) {
            $data = Patient::factory()->make()->toArray();
            $data['created_by'] = $reception?->id;

            $patient = Patient::create($data);

            $daysAgo = match (true) {
                $i < 12 => 0,
                default => fake()->numberBetween(1, 30),
            };
            $timestamp = now()->subDays($daysAgo)->subMinutes(fake()->numberBetween(0, 600));
            $patient->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->save();

            PatientMedicalInfo::create([
                'patient_id' => $patient->id,
                'main_complaint' => fake()->randomElement($this->complaints),
                'allergies' => fake()->randomElement($this->allergies),
                'updated_by' => $reception?->id,
            ]);
        }
    }
}
