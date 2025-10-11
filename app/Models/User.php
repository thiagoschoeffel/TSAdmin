<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
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
        'status',
        'role',
        'permissions',
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
            'permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has a specific permission.
     *
     * @deprecated Use Laravel Policies instead (e.g., $user->can('view', $model))
     * This method is kept for internal use by policies only.
     *
     * @internal This method should only be called from Policy classes
     */
    public function canManage(string $resource, string $ability): bool
    {
        return $this->hasPermission($resource, $ability);
    }

    /**
     * Internal method to check if user has a specific permission.
     * Used by policies to verify granular permissions.
     *
     * @param string $resource The resource name (e.g., 'clients', 'products')
     * @param string $ability The ability name (e.g., 'view', 'create', 'update', 'delete')
     * @return bool
     */
    private function hasPermission(string $resource, string $ability): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return (bool)($permissions[$resource][$ability] ?? false);
    }

    /**
     * Send the email verification notification using the app's custom template.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification($this));
    }
}
