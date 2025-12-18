<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, Actionable;

    const INITIATED = 'INITIATED';
    const DECLINED = 'DECLINED';
    const CAPTURED = 'CAPTURED';
    const FAILED = 'FAILED';
    const UNKNOWN = 'UNKNOWN';
    const CREATED = 'CREATED';
    const PENDING_PAYMENT = 'PENDING_PAYMENT';
    const PAID = 'PAID';
    
    const PENDING = 'PENDING';
    const ERROR = 'ERROR';
    const CANCELLED = 'CANCELLED';
    const COMPLETED = 'COMPLETED';

    // Platform

    const WEB = 'WEB';
    const MOBILE = 'MOBILE';
    const CUSTOM = 'CUSTOM';
    const ANDROID = 'ANDROID';
    const IOS = 'IOS';
    const ADMIN_DASHBOARD = 'ADMIN_DASHBOARD';

    // Currency
    const USD = 'USD';
    const BHD = 'BHD';

    //types
    const WALLET = 'WALLET';
    const FREE = 'FREE';
    const APPLE_PAY = 'APPLE_PAY';
    const BENEFIT_PAY = 'BENEFIT_PAY';
    const CREDIMAX_PAYMENT = 'CREDIMAX_PAYMENT';
    const TAP_PAYMENT = 'TAP_PAYMENT';

    protected $fillable = [
        'user_id',
        'status',
        'charged_price',
        'wallet_id',
        'order_id',
        'platform',
        'origin',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'order_id' => 'integer',
            'charged_price' => 'double',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function isWeb(): bool
    {
        return $this->platform == self::WEB;
    }

    public static function GET_STATUS()
    {
        return [
            static::PENDING => ucwords(strtolower(str_replace('_', ' ', static::PENDING))),
            static::FAILED => ucwords(strtolower(str_replace('_', ' ', static::FAILED))),
            static::CANCELLED => ucwords(strtolower(str_replace('_', ' ', static::CANCELLED))),
            static::ERROR => ucwords(strtolower(str_replace('_', ' ', static::ERROR))),
            static::COMPLETED => ucwords(strtolower(str_replace('_', ' ', static::COMPLETED))),
        ];
    }

    public static function GET_ORIGIN()
    {
        return [
            static::APPLE_PAY => ucwords(strtolower(str_replace('_', ' ', static::APPLE_PAY))),
            static::BENEFIT_PAY => ucwords(strtolower(str_replace('_', ' ', static::BENEFIT_PAY))),
            static::WALLET => ucwords(strtolower(str_replace('_', ' ', static::WALLET))),
            static::CREDIMAX_PAYMENT => ucwords(strtolower(str_replace('_', ' ', static::CREDIMAX_PAYMENT))),
            static::TAP_PAYMENT => ucwords(strtolower(str_replace('_', ' ', static::TAP_PAYMENT))),
        ];
    }

    public static function GET_PLATFORM()
    {
        return [
            static::WEB => ucwords(strtolower(str_replace('_', ' ', static::WEB))),
            static::ANDROID => ucwords(strtolower(str_replace('_', ' ', static::ANDROID))),
            static::IOS => ucwords(strtolower(str_replace('_', ' ', static::IOS))),
            static::MOBILE => ucwords(strtolower(str_replace('_', ' ', static::MOBILE))),
            static::ADMIN_DASHBOARD => ucwords(strtolower(str_replace('_', ' ', static::ADMIN_DASHBOARD))),
        ];
    }
}
