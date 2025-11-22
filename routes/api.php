<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/logout', [AuthController::class, 'logout']);

Route::apiResource('menu', MenuController::class);
Route::get('/menu-images/{filename}', function ($filename) {
    $path = public_path('images/menu/' . $filename);
    return response()->file($path);
});

Route::apiResource('promo', PromoController::class);
Route::apiResource('karir', CareerController::class);
Route::apiResource('member', MemberController::class);
Route::apiResource('outlet', OutletController::class);
Route::apiResource('user', UserController::class);

