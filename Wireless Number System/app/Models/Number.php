<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Number extends Model
{
    use HasFactory, SoftDeletes, Actionable;

    protected $fillable = [
        'phone_number',
        'area_id',
        'carrier_id',
        'city_id',
        'user_id',
        'pin',
        'account_number',
        'is_used',
        'expiry',
        'is_expired'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'is_expired' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function scopeIsUsed($query)
    {
        return $query->where('is_used', true);
    }

    public function scopeIsNotUsed($query)
    {
        return $query->where('is_used', '<>', true);
    }

    public function scopeIsExpired($query)
    {
        return $query->where('is_expired', true);
    }

    public function scopeIsNotExpired($query)
    {
        return $query->where('is_expired', '<>', true);
    }


    public function scopeNotExpired($query)
    {
        return $query->where('expiry', '>', now()->toDateString());
    }   

    public function scopeExpired($query)
    {
        return $query->where('expiry', '<', now()->toDateString());
    }
}
