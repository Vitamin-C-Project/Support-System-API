<?php

use App\Http\Controllers\AssignPicController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\Master\MCompanyController;
use App\Http\Controllers\Master\MProjectController;
use App\Http\Controllers\Master\MServerityController;
use App\Http\Controllers\Master\MSeverityController;
use App\Http\Controllers\Master\MTicketController;
use App\Http\Controllers\Master\MUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TicketStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['guest'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
});
Route::get('/unathenticated', function () {
    return response()->json([
        'message' => 'Unathenticated'
    ]);
})->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ticket_status/index', [TicketStatusController::class, 'index']);
    Route::get('/role/index', [RoleController::class, 'index']);
    Route::get('/auth-check', [LoginController::class, 'authCheck']);
    Route::get('/count-ticket-status', [CompanyDashboardController::class, 'countTicketStatus']);

    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::group(['prefix' => 'user', 'controller' => MUserController::class], function () {
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::get('/show/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');

        Route::patch('/active/{id}', 'activeUser');
    });

    Route::group(['prefix' => 'severity', 'controller' => MSeverityController::class], function () {
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::get('/show/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
    });

    Route::group(['prefix' => 'project', 'controller' => MProjectController::class], function () {
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::get('/show/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
    });

    Route::group(['prefix' => 'ticket', 'controller' => MTicketController::class], function () {
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::get('/show/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
    });

    Route::group(['prefix' => 'company', 'controller' => MCompanyController::class], function () {
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::get('/show/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
    });

    Route::group(['prefix' => 'pic', 'controller' => AssignPicController::class], function () {
        Route::post('/store', 'store');
        Route::post('/store-exist', 'UserExist');
        Route::delete('/delete/{id}', 'destroy');
    });
});
