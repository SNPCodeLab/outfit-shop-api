<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'customer_name',
        'gender',
        'phone',
        'email',
        'address',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(SaleHeader::class, 'customer_id', 'customer_id');
    }
}
