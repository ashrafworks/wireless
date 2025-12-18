<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'country_id',
        'is_active',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(Order::class, 'city_id')
            ->where('order_type', Order::ORDER_TYPE_SELL);
    }

    public function buyerOrders()
    {
        return $this->hasMany(Order::class, 'city_id')
            ->where('order_type', Order::ORDER_TYPE_BUY);
    }

    public function numbers()
    {
        return $this->hasMany(Number::class,'city_id');
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }
}
