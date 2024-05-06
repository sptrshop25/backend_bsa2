<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\AksesAPiController;
use App\Models\Teacher;

class UserController extends Controller
{
    public function info_user(Request $request)
    {
        $apikey = new AksesAPiController;
        $key = $request->header('Authorization');
        $apikey = $apikey->apikey($key);
        if ($apikey == false) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = User::join('data_users', 'users.user_id', '=', 'data_users.user_id')->where('users.user_id', $request->user_id)->first();
        return response()->json($user);
    }

    public function register_teacher(Request $request)
    {
        try {
            validator($request->all(), [
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
            $apikey = new AksesAPiController;
            $key = $request->header('Authorization');
            $apikey = $apikey->apikey($key);
            if ($apikey == false) {
                return response()->json(['message' => 'Unauthorized'], 401);
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
