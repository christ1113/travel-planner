<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 登入驗證 (POST /auth/login)
    public function login(Request $request) 
    {
        
        $validated = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('name', $validated['name'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => '帳號或密碼錯誤'
            ], 401);
        }

        //先刪除該使用者所有舊 token
        $user->tokens()->delete();
        
        $token = $user->createToken('api-token', ['*'], now()->addHours(1))->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }
}