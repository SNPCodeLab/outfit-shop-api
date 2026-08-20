<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    use HasFactory;

    protected $primaryKey = 'color_id';

    protected $fillable = [
        'color_name',
        'hex_code',
        'description',
    ];

    public function getDescriptionAttribute($value): string
    {
        if (empty($value)) {
            return 'A beautiful '.strtolower($this->color_name).' shade for high-end fashion.';
        }

        return $value;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'color_id', 'color_id');
    }
}
