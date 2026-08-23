<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'product_id';

    protected $appends = ['id'];

    public function getIdAttribute(): int
    {
        return (int) $this->product_id;
    }

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
     * Truncate product name to 3 words, strip alphanumeric codes, and ensure a valid name.
     */
    public function setProductNameAttribute($value): void
    {
        if ($value) {
            // 1. Strip alphanumeric codes (words containing digits)
            // e.g., "Shearling Track Top Hul10wcx1858" -> "Shearling Track Top"
            $words = explode(' ', $value);
            $cleanWords = array_filter($words, function ($word) {
                return ! preg_match('/\d/', $word);
            });

            // 2. Truncate to 3 words
            if (count($cleanWords) > 3) {
                $cleanWords = array_slice($cleanWords, 0, 3);
            }

            $value = implode(' ', $cleanWords);
        }

        // 3. Fallback for empty or all-code names
        if (empty(trim($value))) {
            $value = 'Premium Product Item';
        }

        $this->attributes['product_name'] = trim($value);
    }

    /**
     * Priority scope for brands: LV > Puma > Gucci > Others.
     */
    public function scopeWithBrandPriority($query)
    {
        if (DB::getDriverName() === 'sqlite') {
            return $query->orderBy('brand', 'asc');
        }

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
