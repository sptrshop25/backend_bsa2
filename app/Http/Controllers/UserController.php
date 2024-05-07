<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DataUser;
use Illuminate\Http\Request;
use App\Http\Controllers\AksesAPiController;
use App\Models\Teacher;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function info_user(Request $request)
    {
        $user = User::join('data_users', 'users.user_id', '=', 'data_users.user_id')->where('users.user_id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    public function update_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'user_name' => 'required',
            'user_phone' => 'required',
            'user_nickname' => 'required',
            'user_date_of_birth' => 'required',
            'user_address' => 'required',
            'user_profile_picture' => 'required',
            'user_gender' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::where('user_id', $request->user_id);
        $email = $user->first();
        $data_user = DataUser::where('user_id', $request->user_id);
        if ($request->user_email == $email->email) {
            return response()->json(['message' => 'Email cannot be the same'], 400);
        }
        $dataUserData = [
            'user_name' => $request->user_name,
            'user_phone_number' => $request->user_phone,
            'user_nickname' => $request->user_nickname,
            'user_date_of_birth' => $request->user_date_of_birth,
            'user_address' => $request->user_address,
            'user_profile_picture' => $request->user_profile_picture,
            'user_gender' => $request->user_gender,
        ];
        if ($request->user_password !== null) {
            $userData['password'] = bcrypt($request->user_password);
            $user->update($userData);
        }
        $data_user->update($dataUserData);
        return response()->json(['message' => 'Success update user'], 200);
    }

    public function register_teacher(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'academic_degree' => 'required',
                'university' => 'required',
                'major' => 'required',
                'education_experience' => 'required',
                'industries_experience' => 'required',
                'expertise_field' => 'required',
                'instructional_skill' => 'required',
                'link_portfolio' => 'required',
                'certificate' => 'required',
                'term_and_condition' => 'required',
                'faq' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            Teacher::insert([
                'teacher_id' => $request->user_id,
                'teacher_academic_degree' => $request->academic_degree,
                'teacher_university' => $request->university,
                'teacher_major' => $request->major,
                'teacher_education_experience' => $request->education_experience,
                'teacher_industries_experience' => $request->industries_experience,
                'teacher_expertise_field' => $request->expertise_field,
                'teacher_instructional_skill' => $request->instructional_skill,
                'teacher_link_portfolio' => $request->link_portfolio,
                'teacher_certificate' => $request->certificate,
                'teacher_term_and_condition' => $request->term_and_condition,
                'teacher_faq' => $request->faq,
                'teacher_status' => 'Active',
            ]);
            User::where('user_id', $request->user_id)->update(['user_teacher' => 'yes']);
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
