<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Order extends Model
{
    use Actionable, SoftDeletes, HasFactory;

    const  ORDER_TYPE_BUY = 'BUY';
    const ORDER_TYPE_SELL = 'SELL';

    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_REFUNDED = 'REFUNDED';

    const CURRENCY_USD = 'USD';


    protected $fillable = [
        'reference',
        'user_id',
        'carrier_id',
        'city_id',
        'order_type',
        'area_id',
        'reject_qty',
        'success_qty',
        'is_refunded',
        'total_qty',
        'price',
        'subtotal',
        'currency',
        'total',
        'status',
        'refunded_at',
        'notes',
    ];

    public static function GET_STATUS()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function numbers()
    {
        return $this->belongsToMany(Number::class, OrderItem::class, 'order_id', 'number_id');
    }

    public function scopeisRemaining($query)
    {
        return $query->whereRaw('COALESCE(total_qty, 0) - COALESCE(success_qty, 0) > 0');
    }

    public function scopeisRefunded($query)
    {
        return $query->where('is_refunded', true);
    }


    public function scopeisNotRefunded($query)
    {
        return $query->where('is_refunded', false);
    }


    public function scopeisPending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }


    public function scopeIsSelling($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_SELL);
    }

    public function scopeIsBuying($query)
    {
        return $query->where('order_type', self::ORDER_TYPE_BUY);
    }


    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }
}
