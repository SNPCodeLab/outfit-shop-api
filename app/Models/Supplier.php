<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'supplier_name',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseHeader::class, 'supplier_id', 'supplier_id');
    }
}
