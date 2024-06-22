<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

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

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
