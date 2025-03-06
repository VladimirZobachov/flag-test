<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'payment_url',
    ];

    /**
     * Связь "Способ оплаты связан с Заказами".
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Генерация ссылки на оплату для конкретного заказа.
     */
    public function generatePaymentLink(Order $order): string
    {
        return $this->payment_url . '?order_id=' . $order->id;
    }
}
