<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingBanner extends Model
{
    use HasFactory;

    protected $table = 'marketing_banners';

    protected $primaryKey = 'banner_id';

    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'image_public_id',
        'link_url',
        'placement',
        'target_department',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}
