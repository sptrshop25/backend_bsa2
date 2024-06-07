<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Course extends Model
{
    use Searchable;
    protected $primaryKey = 'course_id';
    protected $fillable = ['course_title', 'teacher_id', 'course_description', 'course_price', 'course_category_id', 'course_duration', 'course_level', 'course_is_free', 'course_image', 'created_at'];
}
