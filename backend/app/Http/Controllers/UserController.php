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

    //
}
