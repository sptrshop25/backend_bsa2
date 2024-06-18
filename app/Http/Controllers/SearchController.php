<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SearchHistory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function search_course(Request $request)
    {
        try
        {
        $validator = Validator::make($request->all(), [
            'search_query' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $jwt = $request->bearerToken();
        $user_id = JWT::decode($jwt, new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
        $cek_history = SearchHistory::where('user_id', $user_id)->where('search_query', $request->search_query)->first();
        $search_query = $request->search_query;
        $keyword = explode(' ', $search_query);
        $query = Course::query();
        foreach ($keyword as $key) {
            $query->orWhere('course_title', 'like', '%' . $key . '%');
            $query->orWhere('course_description', 'like', '%' . $key . '%');
        }
        if ($cek_history) {
            $search_course = $query->with(['subCategory.category', 'teacher'])->get();
            return response()->json($search_course, 200);
        }
        $search_count = $query->count();
        $search_history = new SearchHistory();
        $search_history->user_id = $user_id;
        $search_history->search_query = $search_query;
        $search_history->created_at = Carbon::now()->toDateTimeString();
        $search_history->search_count = $search_count;
        $search_history->save();
        $search_course = $query->with(['subCategory.category', 'teacher'])->get();
        return response()->json($search_course, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function search_history(Request $request)
    {
        try
        {
            $jwt = $request->bearerToken();
            $user_id = JWT::decode($jwt, new Key(env('SECRET_KEY_JWT'), 'HS256'))->id;
            $search_history = SearchHistory::where('user_id', $user_id)->get();
            return response()->json($search_history, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function delete_search_history($id)
    {
        try
        {
            $search_history = SearchHistory::find($id);
            $search_history->delete();
            return response()->json(['message' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function delete_search_history_all()
    {
        try
        {
            SearchHistory::query()->delete();
            return response()->json(['message' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
