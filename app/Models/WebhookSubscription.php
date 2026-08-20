<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\CastsBooleanForPostgres;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
