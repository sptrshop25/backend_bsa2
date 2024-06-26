<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialSuccess extends Model
{
    use HasFactory;
    protected $table = 'material_success';
    protected $fillable = ['student_id', 'material_code'];

    public function courseMaterials()
    {
        return $this->belongsTo(CourseMaterial::class, 'material_code', 'material_id');
    }
}
