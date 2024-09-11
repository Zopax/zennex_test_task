<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Регистрация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="test"),
     *             @OA\Property(property="email", type="string", example="test@test.com"),
     *             @OA\Property(property="password", type="string", example="123"),
     *             @OA\Property(property="password_confirmation", type="string", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно зарегистрирован",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function register(Request $request)
    {
        try 
        {
            $fields = $request->validate([
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed'
            ]);
    
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
            ]);
    
            $token = $user->createToken($fields['name']);
    
            return response()->json([
                'message' => 'You are registred.',
                'status' => true,
                'user' => $user,
                'token' => $token->plainTextToken
            ]);
        }
        catch (\Throwable $th) 
        {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

     /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Авторизация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="test@test.com"),
     *             @OA\Property(property="password", type="string", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неправильные учетные данные"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password))
        {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided crendentials are incorrect.'
            ]);
        }

        $token = $user->createToken($user->name);

            return response()->json([
                'message' => 'You are logged in.',
                'user' => $user,
                'token' => $token->plainTextToken
            ]);
    }

     /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Диавторизация пользователя",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="test@test.com"),
     *             @OA\Property(property="password", type="string", example="123")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь диавторизован",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неправильные учетные данные"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'You are logged out.'
        ]);

    }
}
