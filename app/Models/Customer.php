<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'customer_name',
        'gender',
        'phone',
        'email',
        'address',
        'loyalty_points',
        'vip_tier',
        'total_spent_lifetime',
        'store_credit_balance',
    ];

    protected $casts = [
        'loyalty_points' => 'integer',
        'total_spent_lifetime' => 'float',
        'store_credit_balance' => 'float',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(SaleHeader::class, 'customer_id', 'customer_id');
    }

    public function wishlist(): HasMany
    {
        return $this->hasMany(CustomerWishlist::class, 'customer_id', 'customer_id');
    }
}
