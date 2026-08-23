<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $primaryKey = 'employee_id';

    protected $appends = ['id'];

    public function getIdAttribute(): int
    {
        return (int) $this->employee_id;
    }

    protected $fillable = [
        'employee_name',
        'gender',
        'phone',
        'email',
        'position',
        'username',
        'password_hash',
        'role',
        'status',
        'joined_at',
        'avatar_url',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
        'two_factor_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'joined_at' => 'date',
        ];
    }

    /**
     * Return the password attribute so Sanctum's auth driver can verify it.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Canonical uppercase role for this employee.
     */
    public function getRoleLabel(): string
    {
        return strtoupper($this->role ?? 'STAFF');
    }

    /**
     * Whether this employee is a super-admin.
     */
    public function isAdmin(): bool
    {
        return $this->getRoleLabel() === 'ADMIN';
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseHeader::class, 'employee_id', 'employee_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(SaleHeader::class, 'employee_id', 'employee_id');
    }
}
