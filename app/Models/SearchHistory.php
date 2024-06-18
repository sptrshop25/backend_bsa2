<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class SearchHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'search_historys';

    protected $dates = ['deleted_at'];

    protected static function booted()
    {
        static::addGlobalScope('notDeleted', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });
    }
}
