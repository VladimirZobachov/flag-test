<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PaymentMethod;
use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * Получить список доступных способов оплаты.
     */
    public function listMethods()
    {
        $paymentMethods = PaymentMethod::all();

        return response()->json($paymentMethods);
    }

    /**
     * Сгенерировать ссылку на оплату для заказа.
     */
    public function getPaymentLink($orderId)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->findOrFail($orderId);

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Оплата невозможна для этого заказа'], 400);
        }

        $paymentMethod = $order->paymentMethod;
        $paymentLink = $paymentMethod->generatePaymentLink($order);

        return response()->json([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod->name,
            'payment_url' => $paymentLink,
        ]);
    }
}
