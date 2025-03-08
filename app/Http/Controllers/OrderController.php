<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Управление заказами"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/cart/checkout",
     *     summary="Оформить заказ (оплатить корзину)",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method_id"},
     *             @OA\Property(property="payment_method_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Заказ успешно создан"),
     *     @OA\Response(response=400, description="Корзина пуста"),
     *     @OA\Response(response=500, description="Ошибка при создании заказа")
     * )
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
     * @OA\Put(
     *     path="/api/orders/{orderId}/status",
     *     summary="Обновить статус заказа (Оплачен)",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Статус заказа обновлен"),
     *     @OA\Response(response=400, description="Статус заказа нельзя изменить"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
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
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Получить список заказов пользователя",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Фильтрация по статусу заказа",
     *         @OA\Schema(type="string", enum={"На оплату", "Оплачен", "Отменен"})
     *     ),
     *     @OA\Response(response=200, description="Список заказов")
     * )
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
     * @OA\Get(
     *     path="/api/orders/{orderId}",
     *     summary="Получить заказ по ID",
     *     tags={"Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Информация о заказе"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
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
