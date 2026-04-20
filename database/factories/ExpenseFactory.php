<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Transport - Uber',
                'Restaurant - Lunch',
                'Hotel - Business Trip',
                'Office Supplies',
                'Software License',
                'Client Meeting - Coffee',
                'Conference Registration',
                'Flight - Paris to London',
                'Train - Regional Travel',
                'Taxi - Airport Transfer'
            ]),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'description' => fake()->sentence(),
            'proof_file_path' => 'proofs/' . fake()->uuid() . '.pdf',
            'status' => fake()->randomElement(['PENDING', 'APPROVED', 'REJECTED', 'PAID', 'CANCELLED']),
            'rejection_reason' => fn(array $attributes) => 
                $attributes['status'] === 'REJECTED' ? fake()->sentence() : null,
            'payment_method' => fn(array $attributes) => 
                in_array($attributes['status'], ['APPROVED', 'PAID']) ? 
                fake()->randomElement(['Credit Card', 'Bank Transfer', 'Cash']) : null,
            'payment_reference' => fn(array $attributes) => 
                $attributes['status'] === 'PAID' ? 'REF-' . fake()->unique()->numerify('######') : null,
            'paid_at' => fn(array $attributes) => 
                $attributes['status'] === 'PAID' ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'expense_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Indicate that the expense is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
            'rejection_reason' => null,
            'payment_method' => null,
            'payment_reference' => null,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the expense is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'APPROVED',
            'rejection_reason' => null,
            'payment_method' => fake()->randomElement(['Credit Card', 'Bank Transfer', 'Cash']),
            'payment_reference' => null,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the expense is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
            'rejection_reason' => fake()->sentence(),
            'payment_method' => null,
            'payment_reference' => null,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the expense is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PAID',
            'rejection_reason' => null,
            'payment_method' => fake()->randomElement(['Credit Card', 'Bank Transfer', 'Cash']),
            'payment_reference' => 'REF-' . fake()->unique()->numerify('######'),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
