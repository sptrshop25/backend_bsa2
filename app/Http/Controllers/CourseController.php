<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseRating;
use App\Models\CourseSubCategory;
use App\Models\CourseTransaction;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;

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
        try {
            $category_id = CourseSubCategory::where('id', $request->course_category)->get();
            foreach ($category_id as $cat_id) {
                $category_id = $cat_id->course_category_id;
            }
            $category = CourseCategory::where('id', $category_id)->first()->category_name;
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
            $course->course_price_discount = $request->course_price_discount ?? null;
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
        $randomNum = substr(str_shuffle("0123456789"), 0, 4);
        $number = $latestCourse ? intval(substr($latestCourse->course_id, -4)) + 1 : 1;
        // return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        return $prefix . $randomNum;
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
        $courses = Course::with(['subCategory.category', 'teacher'])->get();
        return response()->json($courses);
    }

    public function get_my_courses(Request $request)
    {
        $user_id = JWT::decode($request->bearerToken(), new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
        $courses = Course::with(['subCategory.category', 'teacher'])->where('teacher_id', $user_id)->get();
        return response()->json($courses);
    }
    public function rating_course(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required',
                'rating' => 'required',
                'comment' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $user_id = JWT::decode($request->bearerToken(), new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
            $course = Course::where('course_id', $request->course_id)->first();
            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }
            $courseRating = new CourseRating();
            $courseRating->user_id = $user_id;
            $courseRating->course_id = $request->course_id;
            $courseRating->rating = $request->rating;
            $courseRating->comment = $request->comment;
            $courseRating->updated_at = Carbon::now()->toDateTimeString();
            $courseRating->save();
            $totalRating = round(CourseRating::where('course_id', $request->course_id)->sum('rating') / CourseRating::where('course_id', $request->course_id)->count(), 1);
            Course::where('course_id', $request->course_id)->update(['course_rating' => $totalRating]);
            return response()->json(['message' => 'Course rating updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function transaction_course(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required',
                'transaction_method' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $user_id = JWT::decode($request->bearerToken(), new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
            $user = User::with('dataUser')->where('user_id', $user_id)->first();
            $course = Course::where('course_id', $request->course_id)->first();
            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }
            $transaction_id = $this->generateTransactionCode();
            $priviteKey = env('TRIPAY_PRIVATE_KEY');
            $merchantCode = env('TRIPAY_MERCHANT_CODE');
            $amount = $course->course_price;
            $signature = hash_hmac('sha256', $merchantCode . $transaction_id . $amount, $priviteKey);
            $data = [
                'method' => $request->transaction_method,
                'merchant_ref' => $transaction_id,
                'amount' => $amount,
                'customer_name' => $user->dataUser->user_name,
                'customer_email' => $user->email,
                'customer_phone' => $user->dataUser->user_phone,
                'order_items' => [
                    [
                        'sku'         => $course->course_id,
                        'name'        => $course->course_title,
                        'price'       => $course->course_price,
                        'quantity'    => 1,
                        'product_url' => 'https://tokokamu.com/product/nama-produk-1',
                        'image_url'   => $course->course_image,
                    ],
                    ],
                'return_url' => env('TRIPAY_RETURN_URL'),
                'expired_time' => (time() + (24 * 60 * 60)),
                'signature' => $signature
            ];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('TRIPAY_BASE_URL') . '/transaction/create',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . env('TRIPAY_API_KEY')
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response, true);
            // return response()->json($response, 200);
            $courseTransaction = new CourseTransaction();
            $courseTransaction->transaction_id = $transaction_id;
            $courseTransaction->user_id = $user_id;
            $courseTransaction->transaction_amount = $course->course_price;
            $courseTransaction->transaction_fee_merchant = $response['data']['fee_merchant'];
            $courseTransaction->transaction_fee_customer = $response['data']['fee_customer'];
            $courseTransaction->transaction_total_fee = $response['data']['total_fee'];
            $courseTransaction->transaction_total_amount = $response['data']['amount'];
            $courseTransaction->transaction_method = $response['data']['payment_name'];
            $courseTransaction->transaction_reference = $response['data']['reference'];
            $courseTransaction->transaction_status = $response['data']['status'];
            $courseTransaction->course_id = $request->course_id;
            $courseTransaction->created_at = Carbon::now()->toDateTimeString();
            $courseTransaction->save();
            return response()->json(['message' => 'Course transaction created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    private function generateTransactionCode()
    {
        $latestTransaction = CourseTransaction::orderBy('created_at', 'desc')->first();
        $randomNum = substr(str_shuffle("0123456789"), 0, 10);
        $number = $latestTransaction ? intval(substr($latestTransaction->transaction_id, -4)) + 1 : 1;
        return "INV" . $randomNum;
    }
}
