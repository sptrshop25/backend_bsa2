<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $fillable = ['course_title', 'teacher_id', 'course_description', 'course_price', 'course_category_id', 'course_duration', 'course_level', 'course_is_free', 'course_image', 'created_at'];
}
