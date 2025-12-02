<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GalleryController;
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

Route::post('/change-password', [UserController::class, 'changePassword']);

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
Route::apiResource('galeri', GalleryController::class);
// Routes untuk membership points
Route::post('/members/{id}/add-points', [MemberController::class, 'addPoints']);
Route::post('/members/{id}/redeem-points', [MemberController::class, 'redeemPoints']);
Route::post('/members/{id}/reset-points', [MemberController::class, 'resetPoints']);
Route::get('/members/{id}/points-history', [MemberController::class, 'getPointsHistory']);
Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
Route::get('/dashboard/top-members', [DashboardController::class, 'getTopMembers']);
Route::get('/dashboard/daily-visits', [DashboardController::class, 'getDailyVisits']);
