<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\CourseCategory;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CourseController extends Controller
{
    public function create_course(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_title' => 'required',
            'course_description' => 'required',
            'course_price' => 'required|numeric',
            'course_level' => 'required',
            'course_category' => 'required',
            'course_is_free' => 'required',
            'course_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $jwt = $request->bearerToken();
        $teacher_id = JWT::decode($jwt, new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
        // return $teacher_id;
        try {
            $category = CourseCategory::where('id', $request->course_category)->first()->category_name;
            $imagePath = $request->file('course_image')->store('course_image', 'public');
            $course = new Course($validator->validated());
            $course->course_id = $this->generateCourseCode($category);
            $course->course_title = $request->course_title;
            $course->teacher_id = $teacher_id;
            $course->course_description = $request->course_description;
            $course->course_price = $request->course_price;
            $course->course_category_id = $request->course_category;
            $course->course_duration = $request->course_duration;
            $course->course_level = $request->course_level;
            $course->course_is_free = $request->course_is_free;
            $course->course_image = env('APP_URL') . '/storage/' . $imagePath;
            $course->created_at = Carbon::now()->toDateTimeString();
            $course->save();
            return response()->json(['message' => 'Course created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function generateCourseCode($category)
    {
        $prefix = 'bsa-' . $this->generateCategoryPrefix($category);
        $latestCourse = Course::where('course_id', 'like', $prefix . '%')
            ->orderBy('created_at', 'desc')
            ->first();

        $number = $latestCourse ? intval(substr($latestCourse->course_id, -4)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    private function generateCategoryPrefix($category)
    {
        $words = explode(' ', $category);
        $acronym = '';

        foreach ($words as $word) {
            $acronym .= strtolower($word[0]);
        }

        return $acronym;
    }

    public function get_courses()
    {
        $courses = Course::join('course_categories', 'courses.course_category_id', '=', 'course_categories.id')->join('data_users', 'courses.teacher_id', '=', 'data_users.user_id')->get();
        return response()->json($courses);
    }
}
