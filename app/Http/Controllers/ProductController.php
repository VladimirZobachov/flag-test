<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Управление товарами"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Получить список товаров",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="sort_price",
     *         in="query",
     *         description="Сортировка по цене (asc - по возрастанию, desc - по убыванию)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список товаров",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ноутбук Apple MacBook Pro"),
     *                 @OA\Property(property="description", type="string", example="Мощный ноутбук с процессором M1"),
     *                 @OA\Property(property="price", type="number", format="float", example=1999.99)
     *             )),
     *             @OA\Property(property="total", type="integer", example=50),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="last_page", type="integer", example=5)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Сортировка по цене (asc - по возрастанию, desc - по убыванию)
        if ($request->has('sort_price')) {
            $query->orderBy('price', $request->sort_price === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->paginate(10); // Пагинация по 10 товаров

        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Получить информацию о конкретном товаре",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID товара",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о товаре",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Ноутбук Apple MacBook Pro"),
     *             @OA\Property(property="description", type="string", example="Мощный ноутбук с процессором M1"),
     *             @OA\Property(property="price", type="number", format="float", example=1999.99)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Товар не найден")
     * )
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return response()->json($product);
    }
}
