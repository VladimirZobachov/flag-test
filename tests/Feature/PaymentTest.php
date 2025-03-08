<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentMethod;
use Laravel\Sanctum\Sanctum;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест получения списка способов оплаты.
     */
    public function test_can_get_payment_methods()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Авторизуем пользователя

        PaymentMethod::factory()->count(3)->create();

        $response = $this->getJson('/api/payment/methods');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Тест получения ссылки на оплату заказа.
     */
    public function test_can_get_payment_link()
    {
        $user = User::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod->id,
            'status' => Order::STATUS_PENDING
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/payment/link/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order_id',
                'payment_method',
                'payment_url'
            ]);
    }
}
