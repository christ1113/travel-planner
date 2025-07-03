<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\JourneyController;


//使用者API
Route::get('/users', [UserController::class, 'index']); //抓所有使用者資料
Route::post('/users', [UserController::class, 'store']);//新增使用者
Route::get('/users/{id}', [UserController::class, 'show']);//抓單一使用者
Route::put('/users/{id}', [UserController::class, 'update']);//更新使用者
Route::delete('/users/{id}', [UserController::class, 'destroy']);//刪除使用者

//登入API
Route::post('/auth/login', [AuthController::class, 'login']);//登入驗證

//計畫API
Route::middleware('auth:sanctum')->post('/plan', [PlanController::class, 'store']);//新增計畫

//行程API
Route::middleware('auth:sanctum')->post('/journeys', [JourneyController::class, 'store']);//新增行程