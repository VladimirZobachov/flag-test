<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        PaymentMethod::insert([
            [
                'name' => 'Visa',
                'payment_url' => 'https://visa.com/payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MasterCard',
                'payment_url' => 'https://mastercard.com/payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PayPal',
                'payment_url' => 'https://paypal.com/payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bitcoin',
                'payment_url' => 'https://bitcoin.com/payment',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
