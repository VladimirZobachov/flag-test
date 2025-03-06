<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Оформить заказ (оплатить корзину).
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $user = $request->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Корзина пуста'], 400);
        }

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        DB::beginTransaction();
        try {
            // Создаем заказ
            $order = Order::create([
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'status' => Order::STATUS_PENDING,
                'total_price' => $cart->totalPrice(),
            ]);

            // Переносим товары из корзины в заказ
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // Очищаем корзину
            $cart->items()->delete();

            DB::commit();

            // Генерируем ссылку на оплату
            $paymentUrl = $paymentMethod->generatePaymentLink($order);

            return response()->json([
                'message' => 'Заказ создан',
                'order_id' => $order->id,
                'payment_url' => $paymentUrl,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ошибка при создании заказа', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обновить статус заказа (Оплачен).
     */
    public function updateStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Статус заказа нельзя изменить'], 400);
        }

        $order->markAsPaid();

        return response()->json(['message' => 'Статус заказа обновлен на "Оплачен"']);
    }

    /**
     * Получить список заказов пользователя.
     */
    public function listOrders(Request $request)
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->with('paymentMethod', 'products')
            ->get();

        return response()->json($orders);
    }

    /**
     * Получить заказ по ID.
     */
    public function show($orderId)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $orderId)
            ->with('products', 'paymentMethod')
            ->firstOrFail();

        return response()->json($order);
    }
}
