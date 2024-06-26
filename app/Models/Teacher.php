<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    // protected $table = 'teachers';
    public function dataUser()
    {
        return $this->hasOne(DataUser::class, 'user_id', 'teacher_id');
    }

    public function course()
    {
        return $this->hasMany(Course::class, 'teacher_id', 'teacher_id');
    }

    public function teacherEducationHistory()
    {
        return $this->hasMany(TeacherEducationHistory::class, 'teacher_id', 'teacher_id');
    }

    public function teacherExperience()
    {
        return $this->hasMany(TeacherExperience::class, 'teacher_id', 'teacher_id');
    }
}
