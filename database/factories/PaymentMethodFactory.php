<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PaymentMethod;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Visa', 'MasterCard', 'PayPal', 'Bitcoin']),
            'payment_url' => $this->faker->url(),
        ];
    }
}
