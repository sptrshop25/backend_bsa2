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
    public function info_user()
    {
        $user_id = $this->user_id();
        $user = User::with('dataUser')->where('user_id', $user_id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    public function info_teacher()
    {
        $teacher = Teacher::with(['dataUser.user', 'course.enrollment'])
            // ->join('teacher_balances', 'teacher_balances.teacher_id', '=', 'teachers.teacher_id')
            // ->join('pending_balances', 'pending_balances.teacher_id', '=', 'teachers.teacher_id')
            ->where('teachers.teacher_id', $this->user_id())
            ->first();
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }
        return response()->json($teacher);
    }

    public function update_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'user_phone_number' => 'required',
            'user_nickname' => 'required',
            'user_gender' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user_id = $this->user_id();
        $user = User::where('user_id', $user_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $data_user = DataUser::where('user_id', $user_id)->first();

        $dataUserData = [
            'user_name' => $request->user_name,
            'user_phone_number' => $request->user_phone_number,
            'user_nickname' => $request->user_nickname,
            'user_address' => $request->user_address ?? null,
            'user_gender' => $request->user_gender,
        ];
        if ($request->hasFile('user_profile_picture')) {
            $imagePath = $request->file('user_profile_picture')->store('user_profile_picture', 'public');
            $dataUserData['user_profile_picture'] = env('APP_URL') . '/storage/' . $imagePath;
        }
        // return response()->json($dataUserData);
        DataUser::where('user_id', $user_id)->update($dataUserData);
        if ($request->user_password !== null) {
            $user->update(['password' => bcrypt($request->user_password)]);
        }
        return response()->json(['message' => 'Success update user'], 200);
    }


    public function register_teacher(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'keahlianData.bidangDikuasai' => 'required',
                'keahlianData.bidangDiajarkan' => 'required',
                'teacherData.*.gelar' => 'required',
                'teacherData.*.sekolah' => 'required',
                'teacherData.*.jurusan' => 'required',
                'teacherData.*.tahun_masuk' => 'required|date|',
                'teacherData.*.tahun_lulus' => 'required|date|',
                'pengalamanData.*.judul' => 'required',
                'pengalamanData.*.posisi' => 'required',
                'pengalamanData.*.mulai' => 'required|date',
                // 'pengalamanData.*.selesai' => 'nullable|date|after_or_equal:pengalamanData.*.mulai',
                'pengalamanData.*.deskripsi' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user_id = $this->user_id();

            // Insert teacher main data
            Teacher::insert([
                'teacher_id' => $user_id,
                'teacher_description' => $request->personalData['description'],
                'teacher_expertise_field' => $request->keahlianData['bidangDikuasai'],
                'teacher_instructional_skill' => $request->keahlianData['bidangDiajarkan'],
                'teacher_link_github' => $request->personalData['linkGithub'],
                'teacher_link_linkedin' => $request->personalData['linkLinkedin'],
                'teacher_link_youtube' => $request->personalData['linkYoutube'],
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);

            // Insert education data
            if ($request->has('teacherData')) {
                foreach ($request->teacherData as $education) {
                    TeacherEducationHistory::insert([
                        'teacher_id' => $user_id,
                        'teacher_degree_title' => $education['gelar'],
                        'teacher_university' => $education['sekolah'],
                        'teacher_major' => $education['jurusan'],
                        'teacher_start_year' => $education['tahun_masuk'],
                        'teacher_graduation_year' => $education['tahun_lulus'],
                        'created_at' => Carbon::now()->toDateTimeString(),
                    ]);
                }
            }

            // Insert experience data
            if ($request->has('pengalamanData')) {
                foreach ($request->pengalamanData as $experience) {
                    if ($experience['masihBekerja'] == false) {
                        $endData = $experience['selesai'];
                        $is_still_working = 'yes';
                    } else {
                        $endData = null;
                        $is_still_working = 'no';
                    }
                    TeacherExperience::insert([
                        'teacher_id' => $user_id,
                        'name' => $experience['judul'],
                        'start_date' => $experience['mulai'],
                        'end_date' => $endData,
                        'position' => $experience['posisi'],
                        'description' => $experience['deskripsi'],
                        'is_still_working' => $is_still_working,
                        'created_at' => Carbon::now()->toDateTimeString(),
                    ]);
                }
            }

            // Update user status to teacher
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
                'keahlianData' => 'required',
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
                'teacher_keahlianData' => $request->keahlianData,
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
        $user = User::with('dataUser')->get();
        return response()->json($user);
    }

    private function user_id()
    {
        $jwt = request()->bearerToken();
        $decoded = JWT::decode($jwt, new Key(env('SECRET_KEY_JWT'), 'HS256'));
        return $decoded->id;
    }

    public function delete_user($user_id)
    {
        User::where('user_id', $user_id)->delete();
        DataUser::where('user_id', $user_id)->delete();
        return response()->json(['message' => 'Success'], 200);
    }

    public function teacher_profile($id)
    {
        $teacher = Teacher::with('dataUser', 'course.rating', 'course.enrollment', 'teacherEducationHistory', 'teacherExperience')->where('teacher_id', $id)->first();
        return response()->json($teacher, 200);
    }

    public function list_teacher_top()
    {
        $teachers = Teacher::with('dataUser', 'course.rating', 'course.enrollment', 'teacherEducationHistory', 'teacherExperience')->get();

        // Menghitung nilai maksimum dari setiap kriteria
        $maxCourses = $teachers->max(function ($teacher) {
            return optional($teacher->courses)->count() ?? 0;
        });
        $maxEnrollments = $teachers->max(function ($teacher) {
            return optional($teacher->courses)->sum('enrollments_count') ?? 0;
        });
        $maxRating = $teachers->max(function ($teacher) {
            return optional($teacher->courses)->max('rating') ?? 0;
        });
        $maxAvgRating = $teachers->max(function ($teacher) {
            return optional($teacher->courses)->avg('rating') ?? 0;
        });

        // Memfilter pengajar berdasarkan kriteria maksimum
        $filteredTeachers = $teachers->filter(function ($teacher) use ($maxCourses, $maxEnrollments, $maxRating, $maxAvgRating) {
            $coursesCount = optional($teacher->courses)->count() ?? 0;
            $enrollmentsMax = optional($teacher->courses)->max('enrollments_count') ?? 0;
            $ratingMax = optional($teacher->courses)->max('rating') ?? 0;
            $avgRating = optional($teacher->courses)->avg('rating') ?? 0;

            return $coursesCount == $maxCourses
                || $enrollmentsMax == $maxEnrollments
                || $ratingMax == $maxRating
                || $avgRating == $maxAvgRating;
        });

        // Ambil 10 pengajar teratas dari hasil filter
        $topTeachers = $filteredTeachers->take(10);

        // Format data untuk respons JSON
        $teachersData = $topTeachers->map(function ($teacher) {
            return [
                'teacher_id' => $teacher->teacher_id,
                'name' => $teacher->dataUser->user_name,
                'profile_picture' => $teacher->dataUser->user_profile_picture,
                'courses_count' => optional($teacher->courses)->count() ?? 0,
                'max_enrollments' => optional($teacher->courses)->max('enrollments_count') ?? 0,
                'max_rating' => optional($teacher->courses)->max('rating') ?? 0,
                'avg_rating' => optional($teacher->courses)->avg('rating') ?? 0,
                'education_history' => $teacher->teacherEducationHistory,
                'experience' => $teacher->teacherExperience,
            ];
        });
        return response()->json($teachersData, 200);
    }
}
