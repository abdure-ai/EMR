<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected static array $maleFirstNames = [
        'Abebe', 'Kebede', 'Tesfaye', 'Dawit', 'Yonas', 'Solomon', 'Girma', 'Mulugeta',
        'Bereket', 'Henok', 'Samuel', 'Daniel', 'Yohannes', 'Elias', 'Nathnael', 'Fikadu',
        'Getachew', 'Mesfin', 'Tewodros', 'Alemu', 'Ali', 'Ahmed', 'Mohammed', 'Ibrahim',
    ];

    protected static array $femaleFirstNames = [
        'Mekdes', 'Amina', 'Ruth', 'Selamawit', 'Hana', 'Tigist', 'Marta', 'Bethlehem',
        'Sara', 'Eden', 'Frehiwot', 'Meron', 'Rahel', 'Kidist', 'Lulit', 'Aster',
        'Zufan', 'Genet', 'Almaz', 'Hiwot', 'Fatima', 'Kebron', 'Yordanos', 'Selam',
    ];

    protected static array $fatherNames = [
        'Alemu', 'Girma', 'Bekele', 'Tesfaye', 'Wolde', 'Hailu', 'Kebede', 'Mengistu',
        'Yohannes', 'Gebre', 'Tadesse', 'Desta', 'Abera', 'Negash', 'Fikre', 'Assefa',
        'Demeke', 'Haile', 'Teshome', 'Zeleke', 'Yusuf', 'Ahmed', 'Hassan', 'Omar',
    ];

    protected static array $regions = [
        'Addis Ababa' => ['Region 14'],
        'Amhara' => ['North Gondar', 'South Wollo', 'East Gojjam'],
        'Oromia' => ['West Shewa', 'East Shewa', 'Jimma'],
        'Somali' => ['Jarar', 'Fafan'],
        'Tigray' => ['Central', 'Eastern'],
        'SNNPR' => ['Sidama', 'Hadiya'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sex = $this->faker->randomElement(['male', 'female']);
        $firstName = $this->faker->randomElement($sex === 'male' ? static::$maleFirstNames : static::$femaleFirstNames);
        $region = $this->faker->randomKey(static::$regions);
        $zone = $this->faker->randomElement(static::$regions[$region]);

        return [
            'first_name' => $firstName,
            'middle_name' => $this->faker->randomElement(static::$fatherNames),
            'last_name' => $this->faker->randomElement(static::$fatherNames),
            'sex' => $sex,
            'date_of_birth' => $this->faker->dateTimeBetween('-85 years', '-1 years')->format('Y-m-d'),
            'phone' => '+2519'.$this->faker->unique()->numerify('########'),
            'region' => $region,
            'zone' => $zone,
            'woreda' => $this->faker->optional(0.7)->numerify('Woreda ##'),
            'kebele' => $this->faker->optional(0.6)->numerify('##'),
            'house_no' => $this->faker->optional(0.5)->numerify('###'),
            'preferred_language' => $this->faker->randomElement(['am', 'om', 'en', 'ar']),
            'emergency_contact_name' => $this->faker->optional(0.4)->name(),
            'emergency_contact_phone' => $this->faker->optional(0.4)->numerify('+2519########'),
        ];
    }
}
