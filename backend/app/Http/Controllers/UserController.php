<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //取得所有使用者 (GET /users)
    public function index()
    {
        //取得所有使用者
        $users = User::all();

        //回傳JSON格式的使用者列表
        return response()->json($users);
    }

    //新增使用者(POST /users)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json($user, 201);
    }

    //取得單一使用者 (GET /users/{id})
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    //更新使用者 (PUT /users/{id})
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // 驗證輸入的欄位
        $validated = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed', // 這裡使用confirmed，前端須傳入new_password_confirmation
        ]);

        // 比對舊密碼是否正確
        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json(['message' => 'The old password is incorrect'], 400);
        }

        // 更新密碼，並加密
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    //刪除使用者 (DELETE /users/{id})
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
