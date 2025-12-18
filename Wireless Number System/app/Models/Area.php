<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Area extends Model
{
    use HasFactory, SoftDeletes, Actionable;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function scopeisActive($query)
    {
        return $query->where('is_active', true);
    }

    public function numbers()
    {
        return $this->hasMany(Number::class,'area_id');
    }

    
}
