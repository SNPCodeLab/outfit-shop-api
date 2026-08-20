<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\CastsBooleanForPostgres;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundle extends Model
{
    use CastsBooleanForPostgres;
    use HasFactory;

    protected $table = 'product_bundles';

    protected $primaryKey = 'bundle_id';

    protected $fillable = [
        'bundle_name',
        'sku',
        'barcode',
        'bundle_price',
        'original_total_price',
        'description',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'bundle_price' => 'float',
        'original_total_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class, 'bundle_id', 'bundle_id');
    }
}
