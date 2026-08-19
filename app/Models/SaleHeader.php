<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleHeader extends Model
{
    use HasFactory;

    protected $primaryKey = 'sale_id';

    protected $fillable = [
        'invoice_no',
        'store_id',
        'customer_id',
        'employee_id',
        'sale_date',
        'sub_total',
        'total_amount',
        'discount',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'payment_status',
        'status',
        'notes',
        'idempotency_key',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'sale_id', 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'sale_id', 'sale_id');
    }
}
