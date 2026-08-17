<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $primaryKey = 'movement_id';

    protected $fillable = [
        'store_id',
        'variant_id',
        'movement_type',
        'quantity',
        'stock_before',
        'stock_after',
        'movement_date',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
        'employee_id',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'variant_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
