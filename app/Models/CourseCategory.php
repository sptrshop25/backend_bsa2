<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;

    public function sub_category()
    {
        return $this->hasMany(CourseSubCategory::class, 'course_category_id');
    }
}
