<?php

use App\Http\Controllers\CourseController;
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
    Route::post('info_user', [UserController::class, 'info_user']);
    Route::post('info_teacher', [UserController::class, 'info_teacher']);
    Route::post('register/teacher', [UserController::class, 'register_teacher']);
    Route::post('info_teacher', [UserController::class, 'info_teacher']);
    Route::put('update_user', [UserController::class, 'update_user']);
    Route::put('update_teacher', [UserController::class, 'update_teacher']);
    Route::post('create_course', [CourseController::class, 'create_course']);
    Route::post('register', [LoginController::class, 'register']);
});
Route::middleware('web')->group(function () {
    Route::get('oauth/google/redirect', [LoginController::class, 'googleRedirect'])->name('login.google');
    Route::get('oauth/google/callback', [LoginController::class, 'handleGoogleCallback']);
});
