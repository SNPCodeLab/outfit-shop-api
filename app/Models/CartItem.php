<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'cart_id',
        'variant_id',
        'quantity',
        'unit_price',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }

    public function getLineTotalAttribute(): float
    {
        $price = $this->unit_price ?? $this->variant?->price ?? 0;

        return (float) ($price * $this->quantity);
    }
}
