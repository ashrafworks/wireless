<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Carrier extends Model
{
    use HasFactory, SoftDeletes, Actionable;

    protected $fillable = [
        'name',
        'image',
        'price',
        'is_active',
    ];

    public function numbers()
    {
        return $this->hasMany(Number::class,'carrier_id');
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }
    
}
