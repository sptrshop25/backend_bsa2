<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'transaction_id',
        'transaction_status',
        'transaction_amount',
        'transaction_method',
    ];

    public function course()
    {
        return $this->hasMany(Course::class, 'course_id', 'course_id');
    }
    public function user()
    {
        return $this->hasMany(User::class, 'user_id', 'user_id');
    }
}
