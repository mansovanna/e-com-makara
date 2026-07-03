<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);


    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::delete('/logout', [AuthController::class, 'logout']);

    });
    // block Categoris -----------------------------------------------------------
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories-list', [CategoryController::class, 'getCategoriesList']);

    Route::apiResource('foods', FoodController::class);

    Route::get('food-categories-list', [FoodController::class, 'getFoodCategoriesList']);

    Route::apiResource('tables', TableController::class);

    Route::apiResource('orders', OrderController::class);
    Route::post('/check-out', [OrderController::class, 'checkOut']);
    Route::post('/verify-transaction', [OrderController::class, 'checkVerify']);
    Route::get('/dashboard/orders', [OrderController::class, 'summary']);
    // End Block Oder -----------------------------------------------------------
});


Route::get('/', function () {
    return response()->json([
        'title' => 'You not controller all request, Please request ot admin',
        'message' => 'Request admin!'
    ]);
});
