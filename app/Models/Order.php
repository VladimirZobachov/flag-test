<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'status',
        'total_price',
    ];

    /**
     * Статусы заказов
     */
    public const STATUS_PENDING = 'На оплату';
    public const STATUS_PAID = 'Оплачен';
    public const STATUS_CANCELLED = 'Отменен';

    /**
     * Связь "Заказ принадлежит Пользователю".
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь "Заказ содержит Товары через OrderItem".
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    /**
     * Связь "Заказ содержит OrderItem".
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Связь "Заказ имеет Способ оплаты".
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Проверяет, можно ли оплатить заказ
     */
    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Устанавливает статус "Оплачен"
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => self::STATUS_PAID]);
    }

    /**
     * Устанавливает статус "Отменен"
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
