<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Получить список товаров с возможностью сортировки по цене.
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
     * Получить информацию о конкретном товаре по ID.
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return response()->json($product);
    }
}
