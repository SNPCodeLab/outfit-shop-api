<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\CastsBooleanForPostgres;

class Brand extends Model
{
    use CastsBooleanForPostgres;
    use HasFactory;

    protected $table = 'brands';

    protected $primaryKey = 'brand_id';

    protected $fillable = [
        'brand_name',
        'slug',
        'logo_url',
        'banner_url',
        'country_of_origin',
        'description',
        'website_url',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function getDescriptionAttribute($value): string
    {
        if (empty($value)) {
            return 'Official '.$this->brand_name.' collection featuring premium craftsmanship.';
        }

        return $value;
    }

    public function getLogoUrlAttribute($value): ?string
    {
        if (empty($value) && ($this->brand_name === 'KhmeRiel Signature' || $this->slug === 'khmeriel')) {
            return 'https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png';
        }

        return $value;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id', 'brand_id');
    }
}
