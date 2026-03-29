<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/accounts',[AccountController::class, 'index']);
    Route::post('/accounts',[AccountController::class, 'store']);
    Route::get('/accounts/{id}',[AccountController::class, 'show']);
    Route::post('/accounts/{id}/co-owners',[AccountController::class, 'addCoOwner']);
    Route::delete('/accounts/{id}/co-owners/{userId}',[AccountController::class, 'removeCoOwner']);
    Route::patch('/accounts/{id}/convert',[AccountController::class, 'convert']);
    Route::delete('/accounts/{id}',[AccountController::class, 'requestClosure']);
    Route::get('/accounts/{id}/transactions',[AccountController::class, 'transactions']);

    Route::post('/transfers',[TransferController::class, 'store']);
    Route::get('/transfers/{id}',[TransferController::class, 'show']);

    Route::get('/users/me',[UserController::class, 'me']);
    Route::put('/users/me',[UserController::class, 'update']);
    Route::patch('/users/me/password',[UserController::class, 'changePassword']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/accounts',[AdminController::class, 'allAccounts']);
        Route::patch('/accounts/{id}/block',[AdminController::class, 'block']);
        Route::patch('/accounts/{id}/unblock',[AdminController::class, 'unblock']);
        Route::patch('/accounts/{id}/close',[AdminController::class, 'close']);
    });
});
