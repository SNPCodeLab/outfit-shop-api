<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingOrder extends Model
{
    use HasFactory;

    protected $table = 'shipping_orders';
    protected $primaryKey = 'shipping_id';

    protected $fillable = [
        'sale_id',
        'branch_id',
        'fulfillment_type',
        'courier_name',
        'tracking_number',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_city',
        'shipping_cost',
        'status',
        'dispatched_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_cost' => 'float',
        'dispatched_at' => 'datetime',
        'delivered_at'  => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleHeader::class, 'sale_id', 'sale_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'branch_id', 'branch_id');
    }
}
