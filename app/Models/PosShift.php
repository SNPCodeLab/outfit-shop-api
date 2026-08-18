<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosShift extends Model
{
    use HasFactory;

    protected $table = 'pos_shifts';

    protected $primaryKey = 'shift_id';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'opened_at',
        'closed_at',
        'opening_float_usd',
        'opening_float_khr',
        'cash_sales_usd',
        'cash_sales_khr',
        'card_sales_usd',
        'qr_sales_usd',
        'petty_cash_drops_usd',
        'expected_cash_usd',
        'closing_cash_usd',
        'discrepancy_usd',
        'status',
        'notes',
        'z_report_summary',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_float_usd' => 'float',
        'opening_float_khr' => 'float',
        'cash_sales_usd' => 'float',
        'cash_sales_khr' => 'float',
        'card_sales_usd' => 'float',
        'qr_sales_usd' => 'float',
        'petty_cash_drops_usd' => 'float',
        'expected_cash_usd' => 'float',
        'closing_cash_usd' => 'float',
        'discrepancy_usd' => 'float',
        'z_report_summary' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'branch_id', 'branch_id');
    }
}
