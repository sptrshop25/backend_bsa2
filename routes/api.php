<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LoginAdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TripayCallbackController;
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
Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('register', [LoginController::class, 'register']);
Route::post('resend/email', [LoginController::class, 'resend_verification_email']);
Route::post('request/reset-password', [LoginController::class, 'otp']);
Route::post('verify_otp', [LoginController::class, 'verify_otp']);
Route::post('reset_password', [LoginController::class, 'reset_password']);
Route::post('callback/tripay', [TripayCallbackController::class, 'handleCallback']);
Route::middleware(['auth.jwt'])->group(function () {
    Route::get('info_user', [UserController::class, 'info_user']);
    Route::get('info_teacher', [UserController::class, 'info_teacher']);
    Route::post('register/teacher', [UserController::class, 'register_teacher']);
    Route::post('update_user', [UserController::class, 'update_user']);
    Route::put('update_teacher', [UserController::class, 'update_teacher']);
    Route::post('create_course', [CourseController::class, 'create_course']);
    Route::get('cek_token', [LoginController::class, 'cek_token']);
    Route::get('get_courses', [CourseController::class, 'get_courses']);
    Route::post('rating_course', [CourseController::class, 'rating_course']);
    Route::post('search_course', [SearchController::class, 'search_course']);
    Route::get('search_history', [SearchController::class, 'search_history']);
    Route::delete('search_history/{id}', [SearchController::class, 'delete_search_history']);
    Route::delete('search_history', [SearchController::class, 'delete_search_history_all']);
    Route::get('teacher/list-my-course', [CourseController::class, 'get_my_courses']);
    Route::post('buy-course', [TransactionController::class, 'transaction_course']);
    Route::get('detail-course/{course_id}', [CourseController::class, 'detail_course']);
    Route::get('list-category', [CourseController::class, 'list_category']);
    Route::get('list-sub-category', [CourseController::class, 'list_sub_category']);
    Route::post('save-wishlist', [CourseController::class, 'save_wishlist']);
    Route::delete('remove-wishlist/{course_id}', [CourseController::class, 'delete_wishlist']);
    Route::get('list-payment', [CourseController::class, 'list_payment']);
    Route::get('my-wishlist', [CourseController::class, 'my_wishlist']);
    Route::get('my-course', [CourseController::class, 'my_course']);
    Route::get('material/{course_id}', [CourseController::class, 'get_material']);
    Route::get('list_materi/{course_id}', [CourseController::class, 'list_materi']);
    Route::post('transaction', [TransactionController::class, 'transaction_free']);
    Route::get('detail-materi/{material_id}', [CourseController::class, 'detail_materi']);
    Route::post('material-mark-finish', [CourseController::class, 'mark_material_finished']);
    Route::get('my-transaction', [TransactionController::class, 'my_transaction']);
    Route::get('check_course/{course_id}', [CourseController::class, 'check_course']);
    Route::get('teacher-profile/{user_id}', [UserController::class, 'teacher_profile']);
    Route::get('detail-transaction/{transaction_id}', [TransactionController::class, 'detail_transaction']);
    Route::get('list-teacher-top', [UserController::class, 'list_teacher_top']);
    Route::post('extend-course', [TransactionController::class, 'extend_course']);
});
Route::post('admin/login', [LoginAdminController::class, 'login']);
Route::middleware('admin')->group(function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::get('count_dashboard', [LoginAdminController::class, 'count_dashboard']);
        Route::get('get_user', [UserController::class, 'get_user']);
        Route::delete('delete_user/{user_id}', [UserController::class, 'delete_user']);
        Route::post('add_user', [LoginController::class, 'add_user']);
        Route::post('add-payment', [AdminController::class, 'add_method_payment']);
    });
});
Route::middleware('web')->group(function () {
    Route::get('oauth/google/redirect', [LoginController::class, 'googleRedirect'])->name('login.google');
    Route::get('oauth/google/callback', [LoginController::class, 'handleGoogleCallback']);
});
