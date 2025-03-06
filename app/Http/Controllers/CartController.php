<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="Управление корзиной пользователя"
 * )
 */
class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Получить корзину пользователя",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="cart_id", type="integer", example=1),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer", example=10),
     *                 @OA\Property(property="name", type="string", example="Ноутбук Apple MacBook Pro"),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="price", type="number", format="float", example=1999.99),
     *                 @OA\Property(property="total", type="number", format="float", example=3999.98)
     *             )),
     *             @OA\Property(property="total_price", type="number", format="float", example=3999.98)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Неавторизованный запрос")
     * )
     */
    public function getCart(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.product')->first() ?? Cart::create(['user_id' => $user->id]);

        return response()->json([
            'cart_id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'total' => $item->quantity * $item->product->price
                ];
            }),
            'total_price' => $cart->items->sum(fn ($item) => $item->quantity * $item->product->price),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Добавить товар в корзину",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=10),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Товар добавлен в корзину"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=401, description="Неавторизованный запрос")
     * )
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);
        $product = Product::findOrFail($request->product_id);

        // Проверяем, есть ли уже этот товар в корзине
        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Товар добавлен в корзину']);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/remove",
     *     summary="Удалить товар из корзины",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Товар удален из корзины"),
     *     @OA\Response(response=404, description="Товар не найден"),
     *     @OA\Response(response=401, description="Неавторизованный запрос")
     * )
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Корзина пуста'], 404);
        }

        $cartItem = $cart->items()->where('product_id', $request->product_id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Товар не найден в корзине'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Товар удален из корзины']);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/clear",
     *     summary="Очистить корзину пользователя",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Корзина очищена"),
     *     @OA\Response(response=401, description="Неавторизованный запрос")
     * )
     */
    public function clearCart(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Корзина уже пуста'], 200);
        }

        // Удаляем корзину вместе с товарами
        $cart->items()->delete();
        $cart->delete();

        return response()->json(['message' => 'Корзина очищена']);
    }
}

