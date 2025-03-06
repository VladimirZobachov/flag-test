<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
    ];

    /**
     * Связь "Товар принадлежит многим Корзинам" через CartItem.
     */
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Связь "Товар принадлежит многим Заказам" через OrderItem.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    /**
     * Проверяет, есть ли товар в наличии.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Уменьшает количество товара на складе при покупке.
     */
    public function reduceStock(int $quantity): void
    {
        if ($this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
        } else {
            throw new Exception('Недостаточно товара на складе');
        }
    }
}
