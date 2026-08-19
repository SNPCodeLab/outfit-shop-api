<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreBranch extends Model
{
    use HasFactory;

    protected $table = 'store_branches';

    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_name',
        'branch_code',
        'phone',
        'email',
        'address',
        'city',
        'is_warehouse',
        'is_active',
    ];

    protected $casts = [
        'is_warehouse' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(StoreInventory::class, 'branch_id', 'branch_id');
    }
}
