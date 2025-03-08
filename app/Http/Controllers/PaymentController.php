<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\PaymentMethod;
use App\Models\Order;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="Управление оплатой"
 * )
 */
class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/payment/methods",
     *     summary="Получить список доступных способов оплаты",
     *     tags={"Payments"},
     *     @OA\Response(
     *         response=200,
     *         description="Список доступных способов оплаты",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Visa"),
     *                 @OA\Property(property="payment_url", type="string", example="https://visa.com/pay")
     *             )
     *         )
     *     )
     * )
     */
    public function listMethods()
    {
        $paymentMethods = PaymentMethod::all();

        return response()->json($paymentMethods);
    }

    /**
     * @OA\Get(
     *     path="/api/payment/link/{orderId}",
     *     summary="Сгенерировать ссылку на оплату заказа",
     *     tags={"Payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка на оплату заказа",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="payment_method", type="string", example="Visa"),
     *             @OA\Property(property="payment_url", type="string", example="https://visa.com/pay/1")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Оплата невозможна для этого заказа"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
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
