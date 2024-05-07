<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DataUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $credential = $validator->validated();
            if (Auth::attempt($credential)) {
                $user = Auth::user();
                $payload = [
                    'iss' => 'bsa-api',
                    'id' => $user->user_id,
                    'email' => $user->email,
                    'role' => $user->user_role,
                    'iat' => 1620386280,
                    'nbf' => 1620398400,
                ];
                $jwt = JWT::encode($payload, env('SECRET_KEY_JWT'), 'HS256');
                $user_signin_key = Str::random(30);
                User::where('user_id', $user->user_id)->update(['user_signin_key' => $user_signin_key]);
                return response()->json([
                    'message' => 'Success',
                    'Bearer ' => $jwt
                ], 200);
            }
            return response()->json([
                'message' => 'Email or password is incorrect'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'fullname' => 'required',
            'nickname' => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'phone' => 'required',
            'focus_area' => 'required',
            'interest_field' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user_id = "bsa-" . Str::random(6);
        User::insert([
            'user_id' => $user_id,
            'email' => $request->email,
            'password' => bcrypt($request->password),
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
            'user_focus_area' => $request->focus_area,
            'user_interest_field' => $request->interest_field,
        ]);
        return response()->json([
            'message' => 'Success',
        ], 200);
    }
}
