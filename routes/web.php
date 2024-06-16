<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('test_email', [LoginController::class, 'test_email']);
Route::get('/verify-email/{token}',[LoginController::class, 'verifyEmail'])->name('verify.email');
Route::get('test', function () {
    return view('info_reset_password');
});
