<?php

namespace App\Http\Controllers;

use App\Mail\InfoResetPassword;
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
use App\Models\Course;

class LoginAdminController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 401);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->user_role != 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not admin',
                ], 401);
            }
            if (Hash::check($request->password, $user->password)) {
                $payload = [
                    'iss' => 'bsa-api',
                    'id' => $user->user_id,
                    'email' => $user->email,
                    'role' => $user->user_role,
                    'iat' => now()->timestamp,
                    'exp' => now()->timestamp + (60 * 60 * 24),
                ];
                $token = JWT::encode($payload, env('SECRET_KEY_JWT'), 'HS256');
                return response()->json([
                    'success' => true,
                    'token' => 'Bearer ' . $token,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Email or Password Incorrect',
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email or Password Incorrect',
            ], 401);
        }
    }

    public function count_dashboard()
    {
        $user = User::count();
        $course = Course::count();
        $student = User::where('user_teacher', 'no')->count();
        $teacher = User::where('user_teacher', 'yes')->count();
        return response()->json([
            'success' => true,
            'count_user' => $user,
            'count_student' => $student,
            'count_teacher' => $teacher,
            'count_course' => $course
        ]);
    }
}
