<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'variant_id';

    protected $fillable = [
        'product_id',
        'size_id',
        'color_id',
        'sku',
        'barcode',
        'image_url',
        'image_public_id',
        'cost_price',
        'sale_price',
        'quantity',
        'reorder_level',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ClothingSize::class, 'size_id', 'size_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id', 'color_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id', 'variant_id');
    }
}
