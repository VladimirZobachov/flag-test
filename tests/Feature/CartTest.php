<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use Laravel\Sanctum\Sanctum;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест добавления товара в корзину.
     */
    public function test_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Товар добавлен в корзину']);
    }

    /**
     * Тест получения корзины.
     */
    public function test_can_get_cart()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Гарантированно создаём корзину перед тестом
        $cart = Cart::create(['user_id' => $user->id]);

        $response = $this->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson(['cart_id' => $cart->id]);
    }

    /**
     * Тест очистки корзины.
     */
    public function test_can_clear_cart()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Гарантированно создаём корзину перед очисткой
        $cart = Cart::create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/cart/clear');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Корзина очищена']);

        // Проверяем, что корзины больше нет
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }
}
