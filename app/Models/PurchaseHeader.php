<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseHeader extends Model
{
    use HasFactory;

    protected $primaryKey = 'purchase_id';

    protected $fillable = [
        'reference_no',
        'supplier_id',
        'employee_id',
        'purchase_date',
        'total_amount',
        'tax_amount',
        'grand_total',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'tax_amount' => 'float',
        'grand_total' => 'float',
        'purchase_date' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'purchase_id');
    }
}
