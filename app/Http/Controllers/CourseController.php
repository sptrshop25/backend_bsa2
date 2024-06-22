<?php

namespace App\Http\Controllers;

use App\Models\Assignments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseEnrollment;
use App\Models\CourseMaterial;
use App\Models\CourseRating;
use App\Models\CourseSubCategory;
use App\Models\CourseTransaction;
use App\Models\MaterialBab;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function create_course(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judulKursus' => 'required|string',
            'deskripsi' => 'required|string',
            'tingkatan' => 'required|string',
            'subBidang' => 'required|string',
            'bannerKursus' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'jenisHarga' => 'required|string|in:free,berbayar',
            'hargaKursus' => 'nullable|numeric',
            'jenisLangganan' => 'required|string|in:unlimited,limited',
            'jumlahBulan' => 'nullable|integer|min:1',
            'babList' => 'required|array',
            'babList.*.judul' => 'required|string',
            'babList.*.subBab' => 'nullable|string',
            'babList.*.materi' => 'nullable|file|mimes:mp4,mov,avi,wmv',
            'babList.*.deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Proses data yang diterima
        try {
            // Mendeklarasikan variabel untuk atribut kursus
            $tingkatan = $request->tingkatan;
            switch ($tingkatan) {
                case 'Pemula':
                    $tingkatan = 'beginner';
                    break;
                case 'Menengah':
                    $tingkatan = 'intermediate';
                    break;
                case 'Ahli':
                    $tingkatan = 'advanced';
                    break;
                default:
                    $tingkatan = null;
            }

            // Penanganan harga kursus
            $course_is_free = $request->jenisHarga === 'free' ? 'yes' : 'no';
            $hargaKursus = $request->jenisHarga === 'free' ? 0 : $request->hargaKursus;
            $course_duration = $request->jenisLangganan === 'unlimited' ? null : $request->jumlahBulan;

            // Mendapatkan id kategori berdasarkan sub bidang
            $category_id = CourseSubCategory::where('sub_category_name', $request->subBidang)->firstOrFail()->course_category_id;

            // Mendapatkan nama kategori
            $category = CourseCategory::findOrFail($category_id)->category_name;

            // Generate course ID berdasarkan kategori
            $course_id = $this->generateCourseCode($category);

            // Penyimpanan gambar kursus
            $imagePath = $request->file('bannerKursus')->store('course_image', 'public');

            // Simpan data kursus
            $course = new Course();
            $course->course_id = $course_id;
            $course->course_title = $request->judulKursus;
            $course->teacher_id = JWT::decode($request->bearerToken(), new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
            $course->course_description = $request->deskripsi;
            $course->course_price = $hargaKursus;
            $course->course_category_id = $category_id;
            $course->course_duration = $course_duration;
            $course->course_level = $tingkatan;
            $course->course_is_free = $course_is_free;
            // if ($request->hargaDiskon < 1) {
            //     $course->course_price_discount = $request->hargaDiskon;
            // }
            $course->course_image = env('APP_URL') . '/storage/' . $imagePath;
            $course->created_at = Carbon::now()->toDateTimeString();
            $course->save();

            foreach ($request->babList as $index => $bab) {
                $material_bab = new MaterialBab();
                $material_bab->course_id = $course_id;
                $material_bab->title = $bab['judul'];
                $material_bab->bab = $index + 1;
                $material_bab->save();
                foreach ($bab['subBabList'] as $subBab) {
                    $material = new CourseMaterial();
                    $material->material_id = $this->generateMaterialCode();
                    if (isset($subBab['materi'])) {
                        $videoPath = $subBab['materi']->store('course_video_materi', 'public');
                        $material->material_file = env('APP_URL') . '/storage/' . $videoPath;
                    }
                    $material->material_bab_id = $material_bab->id;
                    $material->material_sub_title = $subBab['judul'];
                    $material->material_description = $subBab['deskripsi'];
                    $material->save();
                }
            }


            return response()->json(['message' => 'Course created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function get_material($id)
    {
        $materials = CourseMaterial::where('material_id', $id)->get();
        return response()->json($materials);
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
        $courses = Course::with(['subCategory.category', 'teacher'])->whereNot('teacher_id', $this->user_id())->get();
        return response()->json($courses);
    }

    public function detail_course($id)
    {
        $course = Course::with(['subCategory.category', 'teacher', 'materialBab.courseMaterials', 'quiz', 'rating.user.dataUser'])->where('course_id', $id)->first();
        $wishlist = Wishlist::where('user_id', $this->user_id())->where('course_id', $id)->count();
        $count_student = CourseEnrollment::where('course_id', $id)->count();
        $count_bab = MaterialBab::where('course_id', $id)->count();
        $count_video_material = MaterialBab::join('course_materials', 'course_materials.material_bab_id', '=', 'material_babs.id')->where('course_id', $id)->whereNotNull('course_materials.material_file')->count();
        $count_user_rating = CourseRating::where('course_id', $id)->count();
        $count_quiz = Assignments::where('course_id', $id)->count();
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        return response()->json(['course' => $course, 'count_student' => $count_student, 'count_bab' => $count_bab, 'count_video_material' => $count_video_material, 'count_quiz' => $count_quiz, 'count_user_rating' => $count_user_rating, 'wishlist' => $wishlist], 200);
    }
    public function get_my_courses()
    {
        $courses = Course::where('teacher_id', $this->user_id())->get();
        if (!$courses) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        return response()->json($courses);
    }

    public function my_course()
    {
        $courses = CourseEnrollment::with(['teacher', 'materialBab.courseMaterials', 'rating.user.dataUser', 'course'])->where('user_id', $this->user_id())->get();
        if (!$courses) {
            return response()->json(['message' => 'Course not found'], 404);
        }
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

    public function list_category()
    {
        $category = CourseCategory::with('sub_category')->get();
        return response()->json($category, 200);
    }

    public function save_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user_id = $this->user_id();
        $wishlist = new Wishlist();
        $wishlist->user_id = $user_id;
        $wishlist->course_id = $request->course_id;
        $wishlist->created_at = Carbon::now()->toDateTimeString();
        $wishlist->save();
        return response()->json([
            'message' => 'Wishlist created successfully',
        ], 200);
    }

    public function delete_wishlist($id)
    {
        try {
            $user_id = $this->user_id();
            $wishlist = Wishlist::where('user_id', $user_id)->where('course_id', $id)->delete();
            return response()->json([
                'message' => 'Wishlist deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function list_payment()
    {
        $payment = PaymentMethod::all();
        return response()->json($payment, 200);
    }

    public function my_wishlist()
    {
        $user_id = $this->user_id();
        $wishlist = Wishlist::with('course')->where('user_id', $user_id)->get();
        return response()->json($wishlist, 200);
    }

    private function user_id()
    {
        $jwt = JWT::decode(request()->bearerToken(), new Key(env('SECRET_KEY_JWT'), 'HS256'));
        return $jwt->id;
    }

    private function generateMaterialCode()
    {
        $randomNum = substr(str_shuffle("0123456789"), 0, 10);
        return 'MAT' . '-' . $randomNum;
    }
}
