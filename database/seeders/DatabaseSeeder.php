<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        $users = User::factory(10)->create();

        // Create a specific test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create expenses for each user
        $users->each(function ($user) {
            // Create a mix of expenses with different statuses
            Expense::factory(5)->pending()->create(['user_id' => $user->id]);
            Expense::factory(3)->approved()->create(['user_id' => $user->id]);
            Expense::factory(2)->rejected()->create(['user_id' => $user->id]);
            Expense::factory(4)->paid()->create(['user_id' => $user->id]);
            
            // Create some random expenses
            Expense::factory(6)->create(['user_id' => $user->id]);
        });

        $this->command->info('Database seeded successfully!');
        $this->command->info('Created 11 users');
        $this->command->info('Created ' . Expense::count() . ' expenses');
    }
}
