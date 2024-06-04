<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DataUser;
use Illuminate\Http\Request;
use App\Http\Controllers\AksesAPiController;
use App\Models\Teacher;
use App\Models\TeacherCertificate;
use App\Models\TeacherEducationHistory;
use App\Models\TeacherExperience;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{
    public function info_user(Request $request)
    {
        $jwt = $request->bearerToken();
        $decoded = JWT::decode($jwt, new Key(env('SECRET_KEY_JWT'), 'HS256'));
        $user = User::join('data_users', 'users.user_id', '=', 'data_users.user_id')->where('users.user_id', $decoded->id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    public function info_teacher(Request $request)
    {
        $teacher = Teacher::where('teacher_id', $request->teacher_id)->first();
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }
        return response()->json($teacher);
    }

    public function update_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        $user_id = $this->user_id();
        $user = User::where('user_id', $user_id);
        $cek_user = $user->first();
        if (!$cek_user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $data_user = DataUser::where('user_id', $user_id);
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
                'expertise_field' => 'required',
                'instructional_skill' => 'required',
                'link_portfolio' => 'required',
                'term_and_condition' => 'required',
                'description' => 'required',
                'teacher_degree_title' => 'required',
                'teacher_university' => 'required',
                'teacher_major' => 'required',
                'teacher_graduation_year' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $user_id = $this->user_id();
            Teacher::insert([
                'teacher_id' => $user_id,
                'teacher_description' => $request->description,
                'teacher_expertise_field' => $request->expertise_field,
                'teacher_instructional_skill' => $request->instructional_skill,
                'teacher_link_portfolio' => $request->link_portfolio,
                'teacher_term_and_condition' => $request->term_and_condition,
                'teacher_status' => 'Active',
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);
            if ($request->has('certificate')) {
                TeacherCertificate::insert([
                    'teacher_id' => $user_id,
                    'certificate' => $request->certificate,
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
            if ($request->has('is_still_working')) {
                TeacherExperience::insert([
                    'teacher_id' => $user_id,
                    'name' => $request->name,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'position' => $request->position,
                    'description' => $request->description_experience,
                    'is_still_working' => $request->is_still_working,
                    'created_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
            TeacherEducationHistory::insert([
                'teacher_id' => $user_id,
                'teacher_degree_title' => $request->teacher_degree_title,
                'teacher_university' => $request->teacher_university,
                'teacher_major' => $request->teacher_major,
                'teacher_graduation_year' => $request->teacher_graduation_year,
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);
            User::where('user_id', $user_id)->update(['user_teacher' => 'yes']);
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update_teacher(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'expertise_field' => 'required',
                'instructional_skill' => 'required',
                'link_portfolio' => 'required',
                'term_and_condition' => 'required',
                'description' => 'required',
                'teacher_degree_title' => 'required',
                'teacher_university' => 'required',
                'teacher_major' => 'required',
                'teacher_graduation_year' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $user_id = $this->user_id();
            Teacher::where('teacher_id', $user_id)->update([
                'teacher_description' => $request->description,
                'teacher_expertise_field' => $request->expertise_field,
                'teacher_instructional_skill' => $request->instructional_skill,
                'teacher_link_portfolio' => $request->link_portfolio,
                'teacher_term_and_condition' => $request->term_and_condition,
            ]);
            if ($request->has('certificate')) {
                TeacherCertificate::where('teacher_id', $user_id)->update([
                    'certificate' => $request->certificate,
                ]);
            }
            if ($request->has('is_still_working')) {
                TeacherExperience::where('teacher_id', $user_id)->update([
                    'name' => $request->name,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'position' => $request->position,
                    'description' => $request->description_experience,
                    'is_still_working' => $request->is_still_working,
                ]);
            }
            TeacherEducationHistory::where('teacher_id', $user_id)->update([
                'teacher_degree_title' => $request->teacher_degree_title,
                'teacher_university' => $request->teacher_university,
                'teacher_major' => $request->teacher_major,
                'teacher_graduation_year' => $request->teacher_graduation_year,
            ]);
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    public function get_user()
    {
        $user = User::join('data_users', 'users.user_id', '=', 'data_users.user_id')->get();
        return response()->json($user);
    }

    private function user_id(){
        $jwt = request()->bearerToken();
        $decoded = JWT::decode($jwt, new Key(env('SECRET_KEY_JWT'), 'HS256'));
        return $decoded->id;
    }
}
