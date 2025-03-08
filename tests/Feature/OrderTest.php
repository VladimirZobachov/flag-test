<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use Laravel\Sanctum\Sanctum;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест оформления заказа.
     */
    public function test_can_checkout_order()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        // Гарантированно создаём корзину
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->postJson('/api/cart/checkout', [
            'payment_method_id' => $paymentMethod->id
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order_id',
                'payment_url'
            ]);
    }
}
