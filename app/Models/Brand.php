<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\CastsBooleanForPostgres;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use CastsBooleanForPostgres;
    use HasFactory;

    protected $table = 'brands';

    protected $primaryKey = 'brand_id';

    protected $appends = ['id'];

    public function getIdAttribute(): int
    {
        return (int) $this->brand_id;
    }

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
