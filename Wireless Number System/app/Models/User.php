<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, Actionable;

    const NTS_ADMINISTRATOR_ROLE = 'NTS_ADMINISTRATOR';
    const SUPER_ADMINISTRATOR_ROLE = 'SUPER_ADMINISTRATOR';
    const SELLER_ROLE = 'SELLER';
    const USER_ROLE = 'USER';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'is_admin',
        'role',
        'parent_user_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static array $rolesLables = [
        self::USER_ROLE => 'Buyer',
        self::SELLER_ROLE => 'Seller',
        self::NTS_ADMINISTRATOR_ROLE => 'NTS Admin',
        self::SUPER_ADMINISTRATOR_ROLE => 'Super Admin',
    ];

    public static function GET_ROLES()
    {
        return [
            self::USER_ROLE => 'Buyer',
            self::SELLER_ROLE => 'Seller',
        ];
    }

    public function scopeIsSuperAdminRole($query)
    {
        return $query->where('role', self::SUPER_ADMINISTRATOR_ROLE);
    }
    public function scopeIsSellerRole($query)
    {
        return $query->where('role', self::SELLER_ROLE);
    }

    public function scopeIsBuyerRole($query)
    {
        return $query->where('role', self::USER_ROLE);
    }

    public function scopeIsAdmin($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    public function superAdmin()
    {
        return $this->role  === self::SUPER_ADMINISTRATOR_ROLE;
    }

    public function ntsAdmin()
    {
        return $this->role  === self::NTS_ADMINISTRATOR_ROLE;
    }

    public function seller()
    {
        return $this->role  === self::SELLER_ROLE;
    }

    public function buyer()
    {
        return $this->role  === self::USER_ROLE;
    }

    public function users()
    {
        return $this->hasMany(self::class, 'parent_user_id');
    }

    public function parant()
    {
        return $this->belongsTo(self::class, 'parent_user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function walletHistories()
    {
        return $this->hasMany(WalletHistory::class, 'user_id');
    }

    public function numbers()
    {
        return $this->hasMany(Number::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function sellerOrders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
