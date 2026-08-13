<?php

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
        'description',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'color_id', 'color_id');
    }
}
