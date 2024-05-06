<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DataUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if ($request->method() == 'POST') {
            validator($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $authorizationHeader = $request->header('Authorization');
            $apikey = "Akses backend briliant skill academy";
            $apikey = hash('sha256', $apikey);
            if ($authorizationHeader !== 'Bearer ' . $apikey) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }
            $user = User::join('data_users', 'users.user_id', '=', 'data_users.user_id')->where('user_email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->user_password)) {
                return response()->json([
                    'message' => 'Email or password is incorrect'
                ], 401);
            }
            if ($user->user_status == 'inactive') {
                return response()->json([
                    'message' => 'User is inactive'
                ], 401);
            }
            try {
                $user_signin_key = Str::random(30);
                User::updated('user_signin_key', $user_signin_key);
                return response()->json([
                    'message' => 'Success',
                    'data' => $user,
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'Failed'
                ], 401);
            }
        } else {
            return response()->json([
                'message' => 'Failed',
            ], 404);
        }
    }
    public function register(Request $request)
    {
        if ($request->method() == 'POST') {
            validator($request->all(), [
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'fullname' => 'required',
                'nickname' => 'required',
                'date_of_birth' => 'required',
                'gender' => 'required',
                'phone' => 'required',
            ]);
            $authorizationHeader = $request->header('Authorization');
            $apikey = "Akses backend briliant skill academy";
            $apikey = hash('sha256', $apikey);
            if ($authorizationHeader !== 'Bearer ' . $apikey) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }
            $user = User::where('user_email', $request->email)->first();
            if ($user) {
                return response()->json([
                    'message' => 'Email already exists'
                ], 401);
            }
            $user_id = "bsa-" . Str::random(6);
            User::insert([
                'user_id' => $user_id,
                'user_email' => $request->email,
                'user_password' => Hash::make($request->password),
                'user_signin_key' => Str::random(30),
                'user_role' => 'user',
                'user_status' => 'active',
                'user_teacher' => 'no',
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);
            DataUser::insert([
                'user_id' => $user_id,
                'user_name' => $request->fullname,
                'user_nickname' => $request->nickname,
                'user_date_of_birth' => $request->date_of_birth,
                'user_gender' => $request->gender,
                'user_phone_number' => $request->phone,
            ]);
            return response()->json([
                'message' => 'Success',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed',
            ], 404);
        }
    }
}
