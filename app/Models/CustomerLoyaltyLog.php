<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLoyaltyLog extends Model
{
    use HasFactory;

    protected $table = 'customer_loyalty_logs';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'customer_id',
        'sale_id',
        'transaction_type',
        'points',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'points'        => 'integer',
        'balance_after' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleHeader::class, 'sale_id', 'sale_id');
    }
}
