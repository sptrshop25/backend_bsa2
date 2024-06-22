<?php 
namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\CourseTransaction;
use Illuminate\Http\Request;

class TripayCallbackController extends Controller
{
    public function handleCallback(Request $request)
    {
        $json = $request->getContent();
        $callbackSignature = $request->header('X-Callback-Signature', '');
        $privateKey = env('TRIPAY_PRIVATE_KEY');
        $signature = hash_hmac('sha256', $json, $privateKey);
        if ($callbackSignature !== $signature) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }
        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data sent by payment gateway',
            ], 400);
        }
        if ($request->header('X-Callback-Event') !== 'payment_status') {
            return response()->json([
                'success' => false,
                'message' => 'Unrecognized callback event: ' . $request->header('X-Callback-Event'),
            ], 400);
        }

        $invoiceId = $data->merchant_ref;
        $tripayReference = $data->reference;
        $status = strtoupper((string) $data->status);

        if ($data->is_closed_payment === 1) {
            $invoice = CourseTransaction::where('transaction_id', $invoiceId)
                ->where('transaction_reference', $tripayReference)
                ->where('status', 'UNPAID')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found or already paid: ' . $invoiceId,
                ], 404);
            }

            switch ($status) {
                case 'PAID':
                    $invoice->transaction_status = 'PAID';
                    break;

                case 'EXPIRED':
                    $invoice->transaction_status = 'EXPIRED';
                    break;

                case 'FAILED':
                    $invoice->transaction_status = 'FAILED';
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ], 400);
            }

            if (!$invoice->save()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update invoice status',
                ], 500);
            }

            $course_enrollment = new CourseEnrollment();
            $course_enrollment->course_id = $invoice->course_id;
            $course_enrollment->user_id = $invoice->user_id;
            $course_enrollment->active_period = $invoice->active_period;
            $course_enrollment->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Payment not closed'], 400);
    }
}

?>