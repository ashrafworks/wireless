<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Wallet extends Model
{
    use HasFactory, Actionable,SoftDeletes;

    protected $fillable = [
        'user_id',
        'available',
        'total',
        'used',
        'currency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walletHistories()
    {
        return $this->hasMany(WalletHistory::class, 'wallet_id');
    }

}
