<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClothingSize extends Model
{
    use HasFactory;

    protected $primaryKey = 'size_id';

    protected $fillable = [
        'size_name',
        'description',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'size_id', 'size_id');
    }
}
