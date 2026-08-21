<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * One demo login per role. Password is the same across all seed accounts
     * for local/demo convenience only — never do this outside local seeding.
     */
    protected array $users = [
        ['name' => 'Ahmed Yusuf', 'email' => 'admin@nesiha.test', 'role' => 'Super Admin'],
        ['name' => 'Fatima Mohammed', 'email' => 'manager@nesiha.test', 'role' => 'Clinic Manager'],
        ['name' => 'Selamawit Tesfaye', 'email' => 'reception@nesiha.test', 'role' => 'Reception'],
        ['name' => 'Abdulaziz Kemal', 'email' => 'practitioner@nesiha.test', 'role' => 'Practitioner'],
        ['name' => 'Hana Girma', 'email' => 'pharmacist@nesiha.test', 'role' => 'Pharmacist'],
        ['name' => 'Yonas Bekele', 'email' => 'cashier@nesiha.test', 'role' => 'Cashier'],
    ];

    public function run(): void
    {
        foreach ($this->users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
