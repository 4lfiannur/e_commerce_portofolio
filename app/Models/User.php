<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;
use Laravel\Sanctum\HasApiTokens;


/**
 * Class User
 *
 * Represents a user of the e-commerce platform.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $address
 * @property string|null $phone
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageProducts(): bool
    {
        return $this->isAdmin();
    }

    public function canManageOrders(): bool
    {
        return $this->isAdmin();
    }
}
