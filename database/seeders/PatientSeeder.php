<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientMedicalInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    protected array $patients = [
        ['first_name' => 'Mekdes', 'middle_name' => 'Alemu', 'last_name' => 'Tesfaye', 'sex' => 'female', 'dob' => '1990-03-14', 'phone' => '+251911223344', 'lang' => 'am', 'region' => 'Addis Ababa', 'zone' => 'Region 14', 'woreda' => 'Bole', 'kebele' => '03', 'house_no' => '142', 'complaint' => 'Chronic joint pain', 'allergies' => 'None known'],
        ['first_name' => 'Dawit', 'middle_name' => 'Girma', 'last_name' => 'Tadesse', 'sex' => 'male', 'dob' => '1985-07-22', 'phone' => '+251922334455', 'lang' => 'am', 'region' => 'Amhara', 'zone' => 'North Gondar', 'woreda' => 'Gondar Zuria', 'kebele' => '07', 'house_no' => '56', 'complaint' => 'Digestive discomfort', 'allergies' => 'Penicillin'],
        ['first_name' => 'Amina', 'middle_name' => 'Yusuf', 'last_name' => 'Abdi', 'sex' => 'female', 'dob' => '1978-11-02', 'phone' => '+251933445566', 'lang' => 'om', 'region' => 'Oromia', 'zone' => 'West Shewa', 'woreda' => 'Ambo', 'kebele' => '01', 'house_no' => '19', 'complaint' => 'Fatigue and low energy', 'allergies' => null],
        ['first_name' => 'Bilal', 'middle_name' => 'Ahmed', 'last_name' => 'Hassan', 'sex' => 'male', 'dob' => '2001-01-30', 'phone' => '+251944556677', 'lang' => 'ar', 'region' => 'Somali', 'zone' => 'Jarar', 'woreda' => null, 'kebele' => null, 'house_no' => null, 'complaint' => 'Skin irritation', 'allergies' => 'Dust'],
        ['first_name' => 'Ruth', 'middle_name' => 'Kebede', 'last_name' => 'Getachew', 'sex' => 'female', 'dob' => '1995-05-18', 'phone' => '+251955667788', 'lang' => 'en', 'region' => 'Addis Ababa', 'zone' => 'Region 14', 'woreda' => 'Yeka', 'kebele' => '11', 'house_no' => '8', 'complaint' => 'Sleep difficulty', 'allergies' => null],
        ['first_name' => 'Tesfaye', 'middle_name' => 'Bekele', 'last_name' => 'Wolde', 'sex' => 'male', 'dob' => '1968-09-09', 'phone' => '+251966778899', 'lang' => 'am', 'region' => 'Tigray', 'zone' => 'Central', 'woreda' => 'Adwa', 'kebele' => '02', 'house_no' => '77', 'complaint' => 'High blood pressure follow-up', 'allergies' => 'Sulfa drugs'],
    ];

    public function run(): void
    {
        $reception = User::where('email', 'reception@nesiha.test')->first();

        foreach ($this->patients as $data) {
            $patient = Patient::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'last_name' => $data['last_name'],
                    'sex' => $data['sex'],
                    'date_of_birth' => $data['dob'],
                    'preferred_language' => $data['lang'],
                    'region' => $data['region'],
                    'zone' => $data['zone'],
                    'woreda' => $data['woreda'],
                    'kebele' => $data['kebele'],
                    'house_no' => $data['house_no'],
                    'created_by' => $reception?->id,
                ]
            );

            PatientMedicalInfo::firstOrCreate(
                ['patient_id' => $patient->id],
                [
                    'main_complaint' => $data['complaint'],
                    'allergies' => $data['allergies'],
                    'updated_by' => $reception?->id,
                ]
            );
        }
    }
}
