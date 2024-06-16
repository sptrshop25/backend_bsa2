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
}
