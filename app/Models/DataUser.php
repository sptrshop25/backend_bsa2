<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataUser extends Model
{
    use HasFactory;
    protected $table = 'data_users';
    protected $fillable = [
        'user_id',
        'user_name',
        'user_nickname',
        'user_gender',
        'user_phone_number',
    ];
}
