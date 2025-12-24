<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'first_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Get the staff record associated with the user
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Get the admin record associated with the user
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get all leave records for this user
     */
    public function leaves()
    {
        return $this->hasManyThrough(\App\Models\Leave::class, \App\Models\Staff::class, 'user_id', 'staff_id', 'id', 'id');
    }

    /**
     * Get all overtime records for this user
     */
    public function overtimes()
    {
        return $this->hasManyThrough(\App\Models\Overtime::class, \App\Models\Staff::class, 'user_id', 'staff_id', 'id', 'id');
    }

    /**
     * Get all OT claims for this user
     */
    public function otClaims()
    {
        return $this->hasMany(OTClaim::class);
    }

    /**
     * Get all payroll records for this user
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
