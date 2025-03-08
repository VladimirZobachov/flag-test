<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Авторизация и регистрация пользователей"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Регистрация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Успешная регистрация"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Авторизация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Успешный вход"),
     *     @OA\Response(response=401, description="Неверные учетные данные")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Успешный вход',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Выход пользователя",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Выход выполнен"),
     *     @OA\Response(response=401, description="Неавторизованный запрос")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Выход выполнен']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Получение информации о текущем пользователе",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Данные пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="email", type="string", format="email", example="ivan@example.com")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Неавторизованный запрос")
     * )
     */
    public function userProfile(Request $request)
    {
        return response()->json($request->user());
    }
}


