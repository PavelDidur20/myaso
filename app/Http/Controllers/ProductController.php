<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API для работы с товарами"
 * )
 */
class ProductController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Список товаров",
     *     @OA\Parameter(
     *         name="name", in="query", description="Фильтр по названию", @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category", in="query", description="Фильтр по категории", @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price_start", in="query", description="Минимальная цена", @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="price_end", in="query", description="Максимальная цена", @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="per_page", in="query", description="Количество на страницу", @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('name'))

            $query->where('name', 'like', '%' . $request->input('name') . '%');


        if ($request->filled('category'))
            $query->where('category', 'like', '%' . $request->input('category') . '%');


        if ($request->filled('price_start'))
            $query->where('price', '>=',  $request->input('price_start'));



        if ($request->filled('price_end'))
            $query->where('price', '<=', $request->input('price_end'));




        $perPage = $request->input('per_page', 10);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $perPage,
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage()
            ]
        ]);
    }
}
