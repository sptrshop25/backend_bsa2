<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
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
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use App\Mail\TestEmail;

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
                if ($user->user_email_verified == 'no') {
                    return response()->json([
                        'message' => 'Email not verified',
                    ], 401);
                }
                $payload = [
                    'iss' => 'bsa-api',
                    'id' => $user->user_id,
                    'email' => $user->email,
                    'role' => $user->user_role,
                    'iat' => now()->timestamp,
                    // 'exp' => now()->timestamp + (60 * 60 * 24),
                ];
                $jwt = JWT::encode($payload, env('SECRET_KEY_JWT'), 'HS256');
                $user_signin_key = Str::random(30);
                User::where('user_id', $user->user_id)->update(['user_signin_key' => $user_signin_key]);
                return response()->json([
                    'message' => 'Success',
                    'Token' => 'Bearer ' . $jwt,
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
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'fullname' => 'required',
                'nickname' => 'required',
                'gender' => 'required',
                'phone' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $cek_nickname = DataUser::where('user_nickname', $request->nickname)->first();
            if ($cek_nickname) {
                return response()->json([
                    'message' => 'Nickname already exist'
                ], 400);
            }
            $cek_phone = DataUser::where('user_phone_number', $request->phone)->first();
            if ($cek_phone) {
                return response()->json([
                    'message' => 'Phone already exist'
                ], 400);
            }
            $user_id = "bsa-" . Str::random(6);
            User::create([
                'user_id' => $user_id,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'user_signin_key' => Str::random(30),
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);
            DataUser::create([
                'user_id' => $user_id,
                'user_name' => $request->fullname,
                'user_nickname' => $request->nickname,
                'user_gender' => $request->gender,
                'user_phone_number' => $request->phone,
            ]);
            $data_user = DataUser::join('users', 'users.user_id', '=', 'data_users.user_id')->where('users.user_id', $user_id)->first();
            $verificationToken = $this->generateVerificationToken();
            User::where('user_id', $user_id)->update(['verification_token' => $verificationToken]);
            Mail::to($request->email)->send(new VerificationEmail($data_user, $verificationToken));
            return response()->json([
                'message' => 'Success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function generateVerificationToken()
    {
        return Str::random(60);
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $cek_user = User::where('email', $user->email)->where('user_is_google', 1)->first();
            if ($cek_user) {
                $payload = [
                    'iss' => 'bsa-api',
                    'id' => $cek_user->user_id,
                    'email' => $cek_user->email,
                    'role' => $cek_user->user_role,
                    'iat' => now()->timestamp,
                    // 'exp' => now()->timestamp + (60 * 60 * 24),
                ];
                $jwt = JWT::encode($payload, env('SECRET_KEY_JWT'), 'HS256');
                return response()->json([
                    'message' => 'Success',
                    'Token' => 'Bearer ' . $jwt
                ], 200);
            } else {
                $cek_user = User::where('email', $user->email)->first();
                if ($cek_user) {
                    return response()->json([
                        'message' => 'Email already exist'
                    ], 400);
                } else {
                    $user_id = "bsa-" . Str::random(6);
                    User::insert([
                        'user_id' => $user_id,
                        'email' => $user->email,
                        'password' => bcrypt($user->id . $user->name . $user->email),
                        'user_is_google' => 1,
                        'user_signin_key' => Str::random(30),
                        'created_at' => Carbon::now()->toDateTimeString(),
                    ]);
                    DataUser::insert([
                        'user_id' => $user_id,
                        'user_name' => $user->name,
                    ]);
                    $payload = [
                        'iss' => 'bsa-api',
                        'id' => $user_id,
                        'email' => $user->email,
                        'role' => 'user',
                        'iat' => now()->timestamp,
                        // 'exp' => now()->timestamp + (60 * 60 * 24),
                    ];
                    $jwt = JWT::encode($payload, env('SECRET_KEY_JWT'), 'HS256');
                    return response()->json([
                        'message' => 'Success',
                        'Token' => 'Bearer ' . $jwt
                    ]);
                }
            }
        } catch (\Exception $e) {
            return redirect()->away('http://127.0.0.1:8000/api/oauth/google/redirect');
            // return $e->getMessage();
        }
    }

    public function cek_token()
    {
        return response()->json([
            'message' => 'Token is valid',
        ]);
    }

    public function verifyEmail($token)
    {
        $user = User::join('data_users', 'users.user_id', '=', 'data_users.user_id')->where('verification_token', $token)->first();
        // dd($user);
        if ($user) {
            if ($user->verification_token === $token) {
                User::where('email', $user->email)->update(['verification_token' => null, 'user_email_verified' => 'yes']);
                return view('redirect')->with('user', $user);
            } else {
                abort(404);
            }
        } else {
            abort(403);
        }
    }

    public function resend_verification_email(Request $request)
    {
        try {
            $data_user = DataUser::join('users', 'users.user_id', '=', 'data_users.user_id')->where('users.email', $request->email)->first();
            $verificationToken = $this->generateVerificationToken();
            User::where('user_id', $data_user->user_id)->update(['verification_token' => $verificationToken]);
            Mail::to($request->email)->send(new VerificationEmail($data_user, $verificationToken));
            return response()->json([
                'message' => 'Success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();
    //     return response()->json([
    //         'message' => 'Success',
    // }

    public function otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data_user = DataUser::join('users', 'users.user_id', '=', 'data_users.user_id')->where('users.email', $request->email)->first();
        if ($data_user) {
            $otp = rand(100000, 999999);
            User::where('user_id', $data_user->user_id)->update(['otp' => $otp]);
            Mail::to($request->email)->send(new OtpMail($data_user, $otp));
            return response()->json([
                'message' => 'Success',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Email not found',
            ], 400);
        }
    }

    public function verify_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data_user = DataUser::join('users', 'users.user_id', '=', 'data_users.user_id')->where('users.email', $request->email)->first();
        $otp = User::where('user_id', $data_user->user_id)->first();
        if ($otp->otp == $request->otp) {
            User::where('user_id', $data_user->user_id)->update(['otp' => null]);
            return response()->json([
                'message' => 'Success',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Wrong otp',
            ], 400);
        }
    }
}
