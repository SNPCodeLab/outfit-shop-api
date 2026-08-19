<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'sale_detail_id';

    protected $fillable = [
        'sale_id',
        'variant_id',
        'quantity',
        'unit_price',
        'discount',
        'sub_total',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'discount' => 'float',
        'sub_total' => 'float',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(SaleHeader::class, 'sale_id', 'sale_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleHeader::class, 'sale_id', 'sale_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }
}
