<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'token_name',
        'method',
        'path',
        'ip',
        'status',
        'duration_ms',
        'response_size',
    ];

    protected $casts = [
        'duration_ms' => 'float',
        'status' => 'integer',
        'response_size' => 'integer',
    ];
}
