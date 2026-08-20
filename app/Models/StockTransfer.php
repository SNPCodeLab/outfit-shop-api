<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $primaryKey = 'transfer_id';

    protected $table = 'stock_transfers';

    protected $fillable = [
        'transfer_no',
        'from_branch_id',
        'to_branch_id',
        'status',
        'requested_by',
        'approved_by',
        'shipped_by',
        'received_by',
        'shipped_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id', 'transfer_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by', 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'employee_id');
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'shipped_by', 'employee_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'received_by', 'employee_id');
    }
}
