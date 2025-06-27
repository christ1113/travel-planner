<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//首頁
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

//使用者API
Route::get('/users', [UserController::class, 'index']); //抓所有使用者資料