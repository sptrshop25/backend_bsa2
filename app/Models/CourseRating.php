<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRating extends Model
{
    use HasFactory;
    protected $table = 'course_user_ratings';

    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'comment',
    ];
}
