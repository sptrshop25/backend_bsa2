<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['course_category_id', 'sub_category_name'];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }
}
