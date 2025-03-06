<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест получения списка товаров.
     */
    public function test_can_get_products_list()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Добавляем аутентификацию

        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Проверяем, что вернулось 3 товара
    }

    /**
     * Тест получения конкретного товара по ID.
     */
    public function test_can_get_single_product()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Добавляем аутентификацию

        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ]);
    }
}
