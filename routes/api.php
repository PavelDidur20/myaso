<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;


Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
   
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('orders', OrderController::class)->only(['index','store']);

    Route::get('products', [ProductController::class, 'index']);
});