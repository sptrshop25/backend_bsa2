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
}
