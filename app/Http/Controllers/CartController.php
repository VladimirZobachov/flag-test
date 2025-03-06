<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Получить корзину текущего пользователя.
     */
    public function getCart()
    {
        $user = Auth::user();
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart) {
            return response()->json(['message' => 'Корзина пуста'], 200);
        }

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
            'total_price' => $cart->totalPrice(),
        ]);
    }

    /**
     * Добавить товар в корзину пользователя.
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
     * Удалить товар из корзины.
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
     * Очистить корзину пользователя.
     */
    public function clearCart()
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Корзина уже пуста'], 200);
        }

        $cart->items()->delete();

        return response()->json(['message' => 'Корзина очищена']);
    }
}

