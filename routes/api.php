<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::get('/accounts',[AccountController::class, 'index']);
Route::post('/accounts',[AccountController::class, 'store']);
Route::get('/accounts/{id}',[AccountController::class, 'show']);
Route::post('/accounts/{id}/co-owners',[AccountController::class, 'addCoOwner']);
Route::delete('/accounts/{id}/co-owners/{userId}',[AccountController::class, 'removeCoOwner']);
Route::patch('/accounts/{id}/convert',[AccountController::class, 'convert']);
Route::delete('/accounts/{id}',[AccountController::class, 'requestClosure']);
Route::get('/accounts/{id}/transactions',[AccountController::class, 'transactions']);
