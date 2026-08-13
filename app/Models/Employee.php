<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'employee_id';

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
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseHeader::class, 'employee_id', 'employee_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(SaleHeader::class, 'employee_id', 'employee_id');
    }
}
