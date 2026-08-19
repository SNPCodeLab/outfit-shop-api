<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCard extends Model
{
    use HasFactory;

    protected $table = 'gift_cards';

    protected $primaryKey = 'card_id';

    protected $fillable = [
        'card_code',
        'pin_hash',
        'initial_balance',
        'current_balance',
        'purchaser_customer_id',
        'expiry_date',
        'is_active',
    ];

    protected $hidden = [
        'pin_hash',
    ];

    protected $casts = [
        'initial_balance' => 'float',
        'current_balance' => 'float',
        'expiry_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'purchaser_customer_id', 'customer_id');
    }
}
