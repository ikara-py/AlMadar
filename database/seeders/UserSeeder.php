<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'AlMadar',
            'email' => 'admin@almadar.ma',
            'password' => Hash::make('password123'),
            'date_of_birth' => '1985-01-01',
            'role' => 'admin'
        ]);

        User::create([
            'first_name' => 'Mohammed',
            'last_name' => 'Benali',
            'email' => 'guardian@example.com',
            'password' => Hash::make('password123'),
            'date_of_birth' => '1980-06-15',
            'role' => 'client'
        ]);

        User::create([
            'first_name' => 'Youssef',
            'last_name' => 'Benali',
            'email' => 'minor@example.com',
            'password' => Hash::make('password123'),
            'date_of_birth' => now()->subYears(14)->format('Y-m-d'),
            'role' => 'client'
        ]);

        User::create([
            'first_name' => 'Fatima',
            'last_name' => 'Chakir',
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
            'date_of_birth' => '1992-03-22',
            'role' => 'client'
        ]);
    }
}
