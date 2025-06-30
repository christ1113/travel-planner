<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//首頁
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});