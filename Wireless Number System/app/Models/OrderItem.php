<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'number_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function number()
    {
        return $this->belongsTo(Number::class);
    }
}
