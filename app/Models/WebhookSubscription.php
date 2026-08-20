<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\CastsBooleanForPostgres;

class WebhookSubscription extends Model
{
    use CastsBooleanForPostgres;
    use HasFactory;

    protected $table = 'webhook_subscriptions';

    protected $fillable = [
        'url',
        'event_type',
        'secret',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
