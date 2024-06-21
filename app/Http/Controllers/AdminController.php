<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function add_method_payment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'method_code' => 'required',
                'method_name' => 'required',
                'method_image' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->messages(),
                ]);
            }
            $imagePath = $request->file('method_image')->store('paymentmethod', 'public');
            $payment = new PaymentMethod();
            $payment->payment_method_code = $request->input('method_code');
            $payment->payment_method_name = $request->input('method_name');
            $payment->payment_method_image = env('APP_URL') . '/storage/' . $imagePath;
            $payment->save();
            return response()->json([
                'status' => 200,
                'message' => 'Payment Method Added Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
