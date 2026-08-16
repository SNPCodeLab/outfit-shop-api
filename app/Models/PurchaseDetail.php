<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'purchase_detail_id';

    protected $fillable = [
        'purchase_id',
        'variant_id',
        'quantity',
        'cost_price',
        'sub_total',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(PurchaseHeader::class, 'purchase_id', 'purchase_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PurchaseHeader::class, 'purchase_id', 'purchase_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }
}
