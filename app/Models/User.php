<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'employee_id',
    'username', 'phone', 'avatar_url', 'joined_at', 'status',
    'last_login_at', 'last_login_ip',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_verified_at'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    // is_admin is deliberately NOT mass-assignable: privilege flags are set
    // only through explicit forceFill() in audited admin code paths.
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'username',
        'phone',
        'avatar_url',
        'joined_at',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'joined_at' => 'date',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Optional link to the employee record sharing this identity.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
