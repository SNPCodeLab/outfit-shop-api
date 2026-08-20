<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\CastsBooleanForPostgres;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use CastsBooleanForPostgres;
    use HasFactory;

    protected $table = 'product_images';

    protected $primaryKey = 'image_id';

    protected $fillable = [
        'product_id',
        'variant_id',
        'image_url',
        'image_public_id',
        'shot_type',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }
}
