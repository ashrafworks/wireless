<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class WalletHistory extends Model
{
    use Actionable, SoftDeletes, HasFactory;

    const TYPE_DEBIT = 'DEBIT';
    const TYPE_CREDIT = 'CREDIT';
    const TYPE_REFUND = 'REFUND';

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'description',
        'user_id',
        'currency',
        'model_id',
        'model_type',
        'status',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function GET_TYPE()
    {
        return [
            self::TYPE_DEBIT => 'Debit',
            self::TYPE_CREDIT => 'Credit',
            self::TYPE_REFUND => 'Refund',
        ];
    }

    public static function GET_STATUS()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }
}
