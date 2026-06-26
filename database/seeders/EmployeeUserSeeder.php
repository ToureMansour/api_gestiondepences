<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'employee@gestiondepences.com'],
            [
                'name' => 'Employé Test',
                'email' => 'employee@gestiondepences.com',
                'password' => Hash::make('employee123'),
                'role' => 'employee',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee2@gestiondepences.com'],
            [
                'name' => 'Employé Test 2',
                'email' => 'employee2@gestiondepences.com',
                'password' => Hash::make('employee123'),
                'role' => 'employee',
            ]
        );

        $this->command->info('Employee users created successfully.');
    }
}
