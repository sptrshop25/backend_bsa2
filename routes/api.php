<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRequestMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth.api'])->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [LoginController::class, 'register']);
    Route::post('info_user', [UserController::class, 'info_user'])->middleware('admin');
    Route::post('register/teacher', [UserController::class, 'register_teacher']);
    Route::post('info_teacher', [UserController::class, 'info_teacher']);  
    Route::put('update_user', [UserController::class, 'update_user']);
});