<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use Carbon\Carbon;

class CourseController extends Controller
{
    public function create_course(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_title' => 'required',
            'teacher_id' => 'required',
            'course_description' => 'required',
            'course_price' => 'required',
            'course_rating' => 'required',
            'course_duration' => 'required',
            'course_level' => 'required',
            'course_category' => 'required',
            'course_is_free' => 'required',
            'course_image' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        try {
        Course::insert([
            'course_title' => $request->course_title,
            'teacher_id' => $request->teacher_id,
            'course_description' => $request->course_description,
            'course_price' => $request->course_price,
            'course_rating' => $request->course_rating,
            'course_category' => $request->course_category,
            'course_duration' => $request->course_duration,
            'course_level' => $request->course_level,
            'course_is_free' => $request->course_is_free,
            'course_image' => $request->course_image,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
        return response()->json(['message' => 'Course created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
