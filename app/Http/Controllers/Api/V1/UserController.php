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
     * Create User
     * @param Request $request
     * @return User 
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
     * Login The User
     * @param Request $request
     * @return User
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
     * Logout The User
     * @param Request $request
     * @return User
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'You are logged out.'
        ]);

    }
}
