<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    use HasFactory;
    protected $fillable = [
        'material_id',
        'course_id',
        'material_bab',
        'material_title',
        'material_sub_title',
        'material_file',
        'material_description'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
    public function materialBab()
    {
        return $this->belongsTo(MaterialBab::class, 'material_bab_id', 'id');
    }
    
    public function materialSuccess()
    {
        return $this->hasMany(MaterialSuccess::class, 'material_code', 'material_id');
    }
}
