<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialBab extends Model
{
    use HasFactory;
    protected $table = 'material_babs'; 
    protected $fillable = ['bab', 'title']; 
    public function courseMaterials()
    {
        return $this->hasMany(CourseMaterial::class, 'material_bab_id', 'id');
    }
}
