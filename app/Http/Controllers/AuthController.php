<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Auth API",
 *     description="API для аутентификации и авторизации пользователей"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Локальный сервер разработки"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Используйте Bearer токен, полученный при регистрации или входе"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1, description="Уникальный идентификатор пользователя"),
 *     @OA\Property(property="name", type="string", example="Иван Иванов", description="Имя пользователя"),
 *     @OA\Property(property="email", type="string", format="email", example="ivan@example.com", description="Email пользователя"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+79001234567", description="Номер телефона пользователя"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         ),
 *         example={
 *             "email": {"The email field is required.", "The email must be a valid email address."},
 *             "password": {"The password field is required."}
 *         }
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="UnauthorizedError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Операции аутентификации и авторизации"
 * )
 * 
 * @OA\Tag(
 *     name="User",
 *     description="Операции с данными пользователя"
 * )
 */
class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="Регистрация нового пользователя",
     *     description="Создает нового пользователя и возвращает токен доступа",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", maxLength=255, description="Имя пользователя", example="Иван Иванов"),
     *             @OA\Property(property="email", type="string", format="email", description="Email пользователя (должен быть уникальным)", example="ivan@example.com"),
     *             @OA\Property(property="password", type="string", minLength=6, description="Пароль пользователя", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", description="Подтверждение пароля", example="password123"),
     *             @OA\Property(property="phone", type="string", pattern="^\+?\d{10,15}$", description="Номер телефона (опционально)", example="+79001234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно зарегистрирован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", description="Bearer токен для API", example="1|abcd1234efgh5678...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'phone'    =>     'regex:/^\+?\d{10,15}$/'
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => 'regex:/^\+?\d{10,15}$/'
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token'  => $token,
        ], 201);
    }

  /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Authentication"},
     *     summary="Вход пользователя",
     *     description="Аутентифицирует пользователя и возвращает токен доступа",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", description="Email пользователя", example="ivan@example.com"),
     *             @OA\Property(property="password", type="string", description="Пароль пользователя", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный вход",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", description="Bearer токен для API", example="2|xyz9876abc5432...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Неверные учетные данные",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"Неверная почта или пароль."}
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверная почта или пароль.'],
            ]);
        }

        // При необходимости: удаляем старые токены
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token'  => $token,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/me",
     *     tags={"User"},
     *     summary="Получить данные текущего пользователя",
     *     description="Возвращает информацию об аутентифицированном пользователе",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Данные пользователя получены",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user'   => $request->user(),
        ]);
    }

/**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Authentication"},
     *     summary="Выход пользователя",
     *     description="Удаляет текущий токен доступа пользователя",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный выход",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Токен удалён, вы успешно вышли.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Токен удалён, вы успешно вышли.',
        ]);
    }
}
