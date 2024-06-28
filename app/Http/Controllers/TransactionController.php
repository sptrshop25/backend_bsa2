<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseTransaction;
use App\Models\Course;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use App\Models\CourseEnrollment;

class TransactionController extends Controller
{
    public function my_transaction()
    {
        $user_id = $this->user_id();
        $transaction = CourseTransaction::with(['course.rating' => function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        }])->where('user_id', $user_id)->orderBy('created_at', 'desc')->get();        
        return response()->json($transaction, 200);
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
                        'product_url' => '#',
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
            $courseTransaction->transaction_url_checkout = $response['data']['checkout_url'];
            $courseTransaction->transaction_method = $response['data']['payment_name'];
            $courseTransaction->transaction_reference = $response['data']['reference'];
            $courseTransaction->transaction_status = $response['data']['status'];
            $courseTransaction->course_id = $request->course_id;
            $courseTransaction->created_at = Carbon::now()->toDateTimeString();
            $courseTransaction->save();
            return response()->json($response, 200);
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

    private function user_id()
    {
        $jwt = JWT::decode(request()->bearerToken(), new Key(env('SECRET_KEY_JWT'), 'HS256'));
        return $jwt->id;
    }

    public function transaction_free(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user_id = $this->user_id();
        $courseEnrollment = CourseEnrollment::where('course_id', $request->course_id)
            ->where('user_id', $user_id)
            ->first();

        if ($courseEnrollment) {
            return response()->json(['message' => 'Course already purchased'], 400);
        }
        $course = Course::where('course_id', $request->course_id)->first();
        $course_duration = null;
        if ($course && $course->course_duration) {
            $course_duration = Carbon::now()->addMonths($course->course_duration)->toDateTimeString();
        }
        $transaction_code = $this->generateTransactionCode();
        $courseTransaction = new CourseTransaction();
        $courseTransaction->user_id = $user_id;
        $courseTransaction->transaction_id = $transaction_code;
        $courseTransaction->transaction_status = 'PAID';
        $courseTransaction->course_id = $request->course_id;
        $courseTransaction->created_at = Carbon::now()->toDateTimeString();
        $courseTransaction->save();
        $courseEnrollment = new CourseEnrollment();
        $courseEnrollment->user_id = $user_id;
        $courseEnrollment->course_id = $request->course_id;
        $courseEnrollment->created_at = Carbon::now()->toDateTimeString();
        $courseEnrollment->active_period = $course_duration;
        $courseEnrollment->save();
        return response()->json(['message' => 'Course purchased successfully'], 200);
    }

    public function extend_course(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user_id = $this->user_id();
        $courseEnrollment = CourseEnrollment::where('course_id', $request->course_id)
            ->where('user_id', $user_id)
            ->first();
        if (!$courseEnrollment) {
            return response()->json(['message' => 'Course not found'], 400);
        }
        $course = Course::where('course_id', $request->course_id)->first();
        $course_duration = Carbon::now()->addMonths($course->course_duration)->toDateTimeString();
        $courseEnrollment->status = 'active';
        $courseEnrollment->active_period = $course_duration;
        $courseEnrollment->save();
        $transaction_code = $this->generateTransactionCode();
        $courseTransaction = new CourseTransaction();
        $courseTransaction->user_id = $user_id;
        $courseTransaction->transaction_id = $transaction_code;
        $courseTransaction->transaction_status = 'PAID';
        $courseTransaction->course_id = $request->course_id;
        $courseTransaction->created_at = Carbon::now()->toDateTimeString();
        $courseTransaction->save();
        return response()->json(['message' => 'Course extended successfully'], 200);
    }

    public function detail_transaction($id)
    {
        $transaction = CourseTransaction::with('user.dataUser', 'course.teacher', 'course.subCategory')->where('transaction_id', $id)->get();
        return response()->json($transaction, 200);
    }
}
