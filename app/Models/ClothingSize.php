<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClothingSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'size_id';

    protected $appends = ['id'];

    public function getIdAttribute(): int
    {
        return (int) $this->size_id;
    }

    protected $fillable = [
        'size_name',
        'size_code',
        'size_order',
        'description',
    ];

    public function getDescriptionAttribute($value): string
    {
        if (empty($value)) {
            return 'Standard sizing for '.$this->size_name.' apparel.';
        }

        return $value;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'size_id', 'size_id');
    }
}
