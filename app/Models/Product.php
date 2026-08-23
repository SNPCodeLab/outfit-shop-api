<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'product_id';

    protected $casts = [
        'image_url' => 'string',
    ];

    protected $fillable = [
        'category_id',
        'brand_id',
        'product_type',
        'product_name',
        'brand',
        'gender',
        'material_fabric',
        'season_collection',
        'author_artist',
        'isbn_code',
        'description',
        'image_url',
        'image_public_id',
        'featured_badge',
        'status',
    ];

    /**
     * Truncate product name to 4 words automatically.
     */
    public function setProductNameAttribute($value): void
    {
        if ($value) {
            $words = explode(' ', $value);
            if (count($words) > 4) {
                $value = implode(' ', array_slice($words, 0, 4));
            }
        }
        $this->attributes['product_name'] = $value;
    }

    /**
     * Priority scope for brands: LV > Puma > Gucci > Others.
     */
    public function scopeWithBrandPriority($query)
    {
        return $query->orderByRaw("
            CASE
                WHEN brand ILIKE '%Louis Vuitton%' OR brand ILIKE '%LV%' THEN 1
                WHEN brand ILIKE '%Puma%' THEN 2
                WHEN brand ILIKE '%Gucci%' THEN 3
                ELSE 4
            END ASC
        ");
    }

    public function getDescriptionAttribute($value): string
    {
        if (empty($value)) {
            $words = ['Premium', 'Luxury', 'Classic', 'Elegant', 'Modern', 'Handcrafted', 'Signature', 'Exclusive'];

            return $words[array_rand($words)].' '.$this->product_name.' designed for comfort and style.';
        }

        return $value;
    }

    public function getImageUrlAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $value;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function brandRef(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id')->orderBy('sort_order', 'asc');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'product_id')->whereRaw('is_approved is true');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_id', 'product_id')->whereRaw('is_primary is true');
    }
}
