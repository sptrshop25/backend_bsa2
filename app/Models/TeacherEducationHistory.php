<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEducationHistory extends Model
{
    use HasFactory;
    protected $table = 'teacher_education_histories';

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
