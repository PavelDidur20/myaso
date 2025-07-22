<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use App\Services\OrderService;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Управление заказами"
 * )
 *
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     required={"product_id", "count"},
 *     @OA\Property(property="product_id", type="integer", description="ID товара", example=5),
 *     @OA\Property(property="count", type="integer", description="Количество товара", example=2)
 * )
 *
 * @OA\Schema(
 *     schema="OrderSummary",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID заказа", example=1),
 *     @OA\Property(property="user_id", type="integer", description="ID пользователя", example=3),
 *     @OA\Property(property="total_price", type="number", format="float", description="Общая стоимость", example=150.50),
 *     @OA\Property(property="status", type="string", description="Статус заказа", example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID заказа", example=1),
 *     @OA\Property(property="user_id", type="integer", description="ID пользователя", example=3),
 *     @OA\Property(property="total_price", type="number", format="float", description="Общая стоимость", example=150.50),
 *     @OA\Property(property="status", type="string", description="Статус заказа", example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Товары в заказе",
 *         @OA\Items(ref="#/components/schemas/OrderItem")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="fail"),
 *     @OA\Property(property="message", type="string", example="Описание ошибки")
 * )
 */
class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}


      /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Получить список заказов",
     *     description="Возвращает пагинированный список заказов с возможностью фильтрации по пользователю",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=false,
     *         description="ID пользователя для фильтрации",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Элементов на страницу",
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов с пагинацией",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/OrderSummary")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="last_page", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $query = Order::query();

        if ($request->filled('user_id')) {
            $query->where('user_id',    $request->input('user_id') );
        }

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

/**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Создать новый заказ",
     *     description="Создает новый заказ с указанными товарами",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "items"},
     *             @OA\Property(property="user_id", type="integer", description="ID пользователя", example=3),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Список товаров в заказе",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "count"},
     *                     @OA\Property(property="product_id", type="integer", description="ID товара", example=5),
     *                     @OA\Property(property="count", type="integer", description="Количество", example=2, minimum=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заказ успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="order_id", type="integer", example=15),
     *             @OA\Property(property="message", type="string", example="Заказ успешно создан")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации или бизнес-логики",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();
        $createdOrder = null;
        try {
            $createdOrder = $this->orderService->createOrder($data);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'order_id' => $createdOrder->id,
            'message' => 'Заказ успешно создан',
        ], 201);
    }
}
