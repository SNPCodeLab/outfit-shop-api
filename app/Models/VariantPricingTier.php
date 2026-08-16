<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantPricingTier extends Model
{
    use HasFactory;

    protected $table = 'variant_pricing_tiers';
    protected $primaryKey = 'tier_id';

    protected $fillable = [
        'variant_id',
        'min_quantity',
        'max_quantity',
        'unit_price',
        'discount_percentage',
    ];

    protected $casts = [
        'min_quantity'        => 'integer',
        'max_quantity'        => 'integer',
        'unit_price'          => 'float',
        'discount_percentage' => 'float',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }
}
