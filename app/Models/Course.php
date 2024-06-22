<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Course extends Model
{
    use Searchable;
    // protected $primaryKey = 'course_id';
    protected $fillable = ['course_title', 'teacher_id', 'course_description', 'course_price', 'course_category_id', 'course_duration', 'course_level', 'course_is_free', 'course_image', 'created_at'];
    public function subCategory()
    {
        return $this->belongsTo(CourseSubCategory::class, 'course_category_id');
    }

    public function teacher()
    {
        return $this->belongsTo(DataUser::class, 'teacher_id', 'user_id');
    }

    public function materialBab()
    {
        return $this->hasMany(MaterialBab::class, 'course_id', 'course_id');
    }

    public function quiz()
    {
        return $this->hasMany(Assignments::class, 'course_id', 'course_id');
    }

    public function rating()
    {
        return $this->hasMany(CourseRating::class, 'course_id', 'course_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'course_id', 'course_id');
    }
}
