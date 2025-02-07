<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Master\MUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['guest'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::group(['prefix' => 'user', 'controller' => MUserController::class], function () {
        Route::get('/index', [MUserController::class, 'index']);
        Route::post('/store', [MUserController::class, 'store']);
        Route::get('/show/{id}', [MUserController::class, 'show']);
        Route::put('/update/{id}', [MUserController::class, 'update']);
    });
});
