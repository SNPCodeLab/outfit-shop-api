<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'sale_id',
        'payment_date',
        'amount',
        'amount_tendered',
        'change_due',
        'payment_method',
        'payment_status',
        'transaction_ref',
        'reference_number',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleHeader::class, 'sale_id', 'sale_id');
    }
}
