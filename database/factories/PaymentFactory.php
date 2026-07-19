<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'paid_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'method' => $this->faker->randomElement(['cash', 'bank_transfer', 'card', 'other']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}