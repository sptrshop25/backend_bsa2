<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DataUser;
use Illuminate\Http\Request;
use App\Http\Controllers\AksesAPiController;
use App\Models\Teacher;
use App\Models\TeacherCertificate;
use App\Models\TeacherEducationExperience;
use App\Models\TeacherEducationHistory;
use App\Models\TeacherIndustryExperience;
use Carbon\Carbon;
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
        $cek_user = $user->first();
        if (!$cek_user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $data_user = DataUser::where('user_id', $request->user_id);
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
                'expertise_field' => 'required',
                'instructional_skill' => 'required',
                'link_portfolio' => 'required',
                'term_and_condition' => 'required',
                'description' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            Teacher::insert([
                'teacher_id' => $request->user_id,
                'teacher_description' => $request->description,
                'teacher_expertise_field' => $request->expertise_field,
                'teacher_instructional_skill' => $request->instructional_skill,
                'teacher_link_portfolio' => $request->link_portfolio,
                'teacher_term_and_condition' => $request->term_and_condition,
                'teacher_status' => 'Active',
            ]);
            if ($request->has('certificate')) {
                TeacherCertificate::insert([
                    'teacher_id' => $request->user_id,
                    'certificate' => $request->certificate,
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
            if ($request->has('name_school')) {
                TeacherEducationExperience::insert([
                    'teacher_id' => $request->user_id,
                    'name_school' => $request->name_school,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'position' => $request->position,
                    'description' => $request->description_school,
                    'is_still_working' => $request->is_still_working,
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
            if ($request->has('industry_name')) {
                TeacherIndustryExperience::insert([
                    'teacher_id' => $request->user_id,
                    'industry_name' => $request->industry_name,
                    'start_date' => $request->start_date_industry,
                    'end_date' => $request->end_date_industry,
                    'position' => $request->position_industry,
                    'description' => $request->description_industry,
                    'is_still_working' => $request->is_still_working_industry,
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
            if ($request->has('teacher_degree_title')) {
                TeacherEducationHistory::insert([
                    'teacher_id' => $request->user_id,
                    'teacher_degree_title' => $request->teacher_degree_title,
                    'teacher_university' => $request->teacher_university,
                    'teacher_major' => $request->teacher_major,
                    'teacher_graduation_year' => $request->teacher_graduation_year,
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
            User::where('user_id', $request->user_id)->update(['user_teacher' => 'yes']);
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update_teacher(Request $request)
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
            $teacher = Teacher::where('teacher_id', $request->user_id)->first();
            if (!$teacher) {
                return response()->json(['message' => 'Teacher not found'], 404);
            }
            Teacher::where('teacher_id', $request->user_id)->update([
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
            ]);
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
